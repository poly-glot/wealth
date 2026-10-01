FROM europe-west2-docker.pkg.dev/firebase-cloud-491613/firebase-cloud/wp-base:7.1-r2

COPY wp-content /app/public/wp-content

RUN cd /app/public/wp-content/themes/wealth/assets/css \
 && cat base/tokens.css base/base.css base/layout.css atoms/*.css components/*.css > main.css

RUN wp-build
