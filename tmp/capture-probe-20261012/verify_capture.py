from pathlib import Path
from PIL import Image
import json
import shutil
import subprocess

root = Path(__file__).resolve().parent
image = Image.open(root / 'surface-capture.png').convert('RGB')
region = image.crop((400, 900, 1000, 1700))
image_max = max(max(pixel) for pixel in region.getdata())
result = subprocess.run([
    shutil.which('ffmpeg'), '-v', 'error', '-i', str(root / 'system-recording.mp4'),
    '-t', '5', '-vf', 'crop=600:800:400:900,scale=64:64',
    '-f', 'rawvideo', '-pix_fmt', 'rgb24', '-'
], check=True, stdout=subprocess.PIPE)
frame_size = 64 * 64 * 3
frames = len(result.stdout) // frame_size
assert frames > 0 and len(result.stdout) % frame_size == 0
video_max = max(result.stdout)
report = {
    'device': 'Android 17 API 37 emulator; NOT Samsung S25 Android 16',
    'screenshot_protected_region_max_rgb': image_max,
    'video_first_seconds_checked': 5,
    'video_frames_checked': frames,
    'video_protected_region_max_rgb': video_max,
    'passed': image_max == 0 and video_max <= 8,
    'scope': 'Central protected SurfaceView region; separate fake-content native probe only.'
}
(root / 'pixel-verification.json').write_text(json.dumps(report, indent=2), encoding='utf-8')
print(json.dumps(report, indent=2))
assert report['passed'], 'Protected test content may be visible; do not integrate.'
