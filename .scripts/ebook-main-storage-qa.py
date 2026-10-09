"""Local QA coordinator. Owns only its PHP process; snapshots only labelled QA work."""
import argparse
import json
import os
import socket
import subprocess
import threading
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('--php', required=True)
parser.add_argument('--poppler', required=True)
parser.add_argument('--output', required=True)
args = parser.parse_args()
OUTPUT = Path(args.output).resolve()
OUTPUT.mkdir(parents=True, exist_ok=True)
process = None
lock = threading.Lock()
log = (OUTPUT / 'main-api.log').open('ab')
env = dict(os.environ, EBOOK_PDFINFO=str(Path(args.poppler)/'pdfinfo.exe'), EBOOK_PDFTOPPM=str(Path(args.poppler)/'pdftoppm.exe'))

def start_api():
    global process
    with lock:
        if process is None or process.poll() is not None:
            process = subprocess.Popen([args.php, '-d', 'upload_max_filesize=20M', '-d', 'post_max_size=24M',
                '-d', 'max_execution_time=0', '-S', '127.0.0.1:8000',
                str(ROOT/'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php')],
                cwd=ROOT/'public', env=env, stdout=log, stderr=log, creationflags=subprocess.CREATE_NO_WINDOW)
    return {'api_running': process.poll() is None, 'pid': process.pid}

def stop_api():
    with lock:
        if process and process.poll() is None:
            process.terminate()
            process.wait(timeout=10)
    return {'api_running': False}

def snapshot():
    result = subprocess.run([args.php, str(ROOT/'.scripts/ebook-main-qa.php'), 'snapshot'],
        cwd=ROOT, capture_output=True, check=True, creationflags=subprocess.CREATE_NO_WINDOW)
    return json.loads(result.stdout.decode('utf-8'))

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
            if self.path == '/snapshot': return self.respond(snapshot())
            if self.path == '/status': return self.respond({'api_running': process is not None and process.poll() is None})
            self.respond({'error': 'Not found'}, 404)
        except Exception as error:
            self.respond({'error': str(error)}, 500)

    def do_POST(self):
        try:
            if self.path == '/offline': return self.respond(stop_api())
            if self.path == '/online': return self.respond(start_api())
            if self.path == '/checkpoint':
                length = int(self.headers.get('Content-Length', '0'))
                if not 0 < length <= 2_000_000: return self.respond({'error': 'Invalid payload size'}, 400)
                payload = json.loads(self.rfile.read(length))
                if payload.get('stage') not in ['online', 'offline', 'force_stopped', 'resumed', 'failed']:
                    raise ValueError('Unknown checkpoint')
                payload['database'] = snapshot()
                (OUTPUT/(payload['stage']+'.json')).write_text(json.dumps(payload, indent=2, ensure_ascii=False), encoding='utf-8')
                return self.respond({'saved': payload['stage'], 'database': payload['database']})
            if self.path == '/finish':
                self.respond(start_api())
                threading.Thread(target=self.server.shutdown, daemon=True).start()
                return
            self.respond({'error': 'Not found'}, 404)
        except Exception as error:
            self.respond({'error': str(error)}, 500)

with socket.socket() as probe:
    probe.bind(('127.0.0.1', 8000))
server = ThreadingHTTPServer(('127.0.0.1', 8791), Handler)
start_api()
print(json.dumps({'coordinator': 'http://127.0.0.1:8791', 'backend': 'http://127.0.0.1:8000', 'evidence': str(OUTPUT)}), flush=True)
server.serve_forever()
server.server_close()
# Leave the main local backend running, with upload/Poppler settings, after the test.
log.close()
