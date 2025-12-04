FROM php:8.2-apache

# Install Python, FFmpeg (crucial for merging), and Git
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    ffmpeg \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install yt-dlp
RUN pip3 install yt-dlp --break-system-packages

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy files
COPY . /var/www/html/

# Create the downloads folder and set permissions
RUN mkdir -p /var/www/html/downloads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
