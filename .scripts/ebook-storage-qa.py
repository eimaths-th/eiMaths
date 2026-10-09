"""Local Android storage QA coordinator; never connects to the main database."""
import argparse
import hashlib
import json
import socket
import sqlite3
import subprocess
import threading
from datetime import datetime, timezone
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import urlparse

ROOT = Path(__file__).resolve().parents[1]
DATABASE = ROOT / 'storage/app/ebook-demo.sqlite'
PRIVATE = ROOT / 'storage/app/ebook-demo-private'
parser = argparse.ArgumentParser()
parser.add_argument('--php', required=True)
parser.add_argument('--output', required=True)
args = parser.parse_args()
OUTPUT = Path(args.output).resolve()
OUTPUT.mkdir(parents=True, exist_ok=True)
if not DATABASE.is_file():
    raise SystemExit('Existing isolated Demo database required.')
process = None
process_lock = threading.Lock()
server_log = (OUTPUT / 'demo-api.log').open('ab')


def start_api():
    global process
    with process_lock:
        if process is None or process.poll() is not None:
            process = subprocess.Popen([
                args.php, '-d', 'extension=pdo_sqlite', '-d', 'extension=sqlite3',
                '-d', 'max_execution_time=0', '-d', 'upload_max_filesize=20M',
                '-d', 'post_max_size=24M', '-S', '127.0.0.1:8787',
                '-t', 'public', '.scripts/ebook-demo-router.php',
            ], cwd=ROOT, stdout=server_log, stderr=server_log,
                creationflags=subprocess.CREATE_NO_WINDOW)
    return {'api_running': process.poll() is None, 'pid': process.pid}


def stop_api():
    with process_lock:
        if process is not None and process.poll() is None:
            process.terminate()
            process.wait(timeout=10)
    return {'api_running': False}


def sha256(relative):
    target = (PRIVATE / relative).resolve()
    if not target.is_relative_to(PRIVATE.resolve()):
        raise ValueError('File is outside Demo private storage.')
    return hashlib.sha256(target.read_bytes()).hexdigest()


def snapshot():
    with sqlite3.connect(DATABASE.resolve().as_uri() + '?mode=ro', uri=True) as con:
        con.row_factory = sqlite3.Row
        page = con.execute('''SELECT b.id AS book_id, b.code, v.id AS version_id,
            v.number AS edition, v.source_path, p.id AS page_id, p.number,
            p.image_path, c.id AS student_id, c.code AS student_code
            FROM ebook_books b JOIN ebook_versions v ON v.id=b.published_version_id
            JOIN ebook_pages p ON p.version_id=v.id JOIN contacts c ON c.code='DEMO001'
            WHERE b.code='G11' AND p.number=5''').fetchone()
        if page is None:
            raise ValueError('The G11 Demo book, page 5, and DEMO001 must exist.')
        page = dict(page)
        row = con.execute('''SELECT revision, document, updated_at FROM ebook_work
            WHERE student_id=? AND page_id=?''', (page['student_id'], page['page_id'])).fetchone()
        work = dict(row) if row else {'revision': 0, 'document': '{"schema":1,"objects":[]}', 'updated_at': None}
        work['document'] = json.loads(work['document'])
        events = [dict(r) for r in con.execute('''SELECT action, source, metadata, received_at
            FROM ebook_events WHERE student_id=? AND page_id=? ORDER BY id''',
            (page['student_id'], page['page_id']))]
        for event in events:
            event['metadata'] = json.loads(event['metadata'] or '{}')
        return {
            'checked_at': datetime.now(timezone.utc).isoformat(),
            'student_code': page['student_code'], 'student_id': page['student_id'],
            'book_code': page['code'], 'book_id': page['book_id'],
            'page_number': page['number'], 'page_id': page['page_id'],
            'version_id': page['version_id'], 'edition': page['edition'],
            'pdf_sha256': sha256(page['source_path']),
            'page_image_sha256': sha256(page['image_path']),
            'work': work, 'events': events,
        }


class Handler(BaseHTTPRequestHandler):
    def log_message(self, *unused):
        pass

    def respond(self, value, status=200):
        body = json.dumps(value, ensure_ascii=False).encode('utf-8')
        self.send_response(status)
        self.send_header('Content-Type', 'application/json; charset=utf-8')
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self):
        try:
            if self.path == '/snapshot':
                return self.respond(snapshot())
            if self.path == '/status':
                return self.respond({'api_running': process is not None and process.poll() is None})
            self.respond({'error': 'Not found'}, 404)
        except Exception as error:
            self.respond({'error': str(error)}, 500)

    def do_POST(self):
        try:
            path = urlparse(self.path).path
            if path == '/offline':
                return self.respond(stop_api())
            if path == '/online':
                return self.respond(start_api())
            if path == '/checkpoint':
                payload = json.loads(self.rfile.read(int(self.headers['Content-Length'])))
                if payload.get('stage') not in ['online', 'offline', 'force_stopped', 'resumed', 'failed']:
                    raise ValueError('Unknown checkpoint.')
                payload['database'] = snapshot()
                (OUTPUT / (payload['stage'] + '.json')).write_text(
                    json.dumps(payload, indent=2, ensure_ascii=False), encoding='utf-8')
                return self.respond({'saved': payload['stage'], 'database': payload['database']})
            if path == '/finish':
                self.respond({'api_running': process is not None and process.poll() is None})
                threading.Thread(target=self.server.shutdown, daemon=True).start()
                return
            self.respond({'error': 'Not found'}, 404)
        except Exception as error:
            self.respond({'error': str(error)}, 500)


with socket.socket() as probe:
    probe.bind(('127.0.0.1', 8787))
server = ThreadingHTTPServer(('127.0.0.1', 8791), Handler)
start_api()
print(json.dumps({'coordinator': 'http://127.0.0.1:8791', 'evidence': str(OUTPUT)}), flush=True)
server.serve_forever()
# Leave the local Demo API available for the user after the coordinator stops.
server.server_close()
server_log.close()
