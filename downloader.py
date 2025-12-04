import sys
import yt_dlp
import json
import os

if len(sys.argv) < 2:
    print(json.dumps({"error": "No URL provided"}))
    sys.exit()

url = sys.argv[1]
output_folder = "/var/www/html/downloads"

# Configure yt-dlp to download and merge
ydl_opts = {
    'quiet': True,
    'no_warnings': True,
    'format': 'bestvideo+bestaudio/best', # Download best video + best audio
    'merge_output_format': 'mp4',        # Force merge to MP4
    'outtmpl': f'{output_folder}/%(id)s.%(ext)s', # Save as ID.mp4
    # Optional: Limit file size to prevent crashing your server (e.g., 50MB)
    'max_filesize': 50 * 1024 * 1024, 
}

try:
    with yt_dlp.YoutubeDL(ydl_opts) as ydl:
        # This actually downloads and merges the file now
        info = ydl.extract_info(url, download=True)
        
        # Get the filename ID
        video_id = info.get('id')
        ext = info.get('ext')
        if ext != 'mp4': 
            # If yt-dlp merged it, the final extension is mp4
            ext = 'mp4'
            
        filename = f"{video_id}.{ext}"

        # Return the filename so PHP can build the link
        result = {
            "status": "success",
            "title": info.get('title'),
            "filename": filename,
            "duration": info.get('duration')
        }
        
        print(json.dumps(result))

except Exception as e:
    print(json.dumps({"status": "error", "message": str(e)}))
