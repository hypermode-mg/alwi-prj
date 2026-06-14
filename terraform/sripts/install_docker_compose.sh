#!/bin/bash
set -euo pipefail

apt-get update && apt-get install -y \
  curl \
  gnupg \
  ca-certificates \
  software-properties-common

# Установка Docker
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
apt-get update
apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# --- Подготовка persistent-диска ---
DISK_DEV="/dev/vdb"
DATA_MOUNT="/opt/monitoring"

if ! lsblk | grep -q "$DISK_DEV"; then
  echo "Warning: Expected secondary disk $DISK_DEV not found. Fallback to boot disk."
  DATA_MOUNT="/var/monitoring"
else
  if ! blkid | grep -q "$DISK_DEV"; then
    mkfs.xfs "$DISK_DEV"
  fi
  mkdir -p "$DATA_MOUNT"
  mount "$DISK_DEV" "$DATA_MOUNT" || true
  UUID=$(blkid -s UUID -o value "$DISK_DEV")
  echo "UUID=$UUID $DATA_MOUNT xfs defaults,noatime 0 2" >> /etc/fstab
fi

mkdir -p "$DATA_MOUNT/prometheus/data"
mkdir -p "$DATA_MOUNT/prometheus"
mkdir -p "$DATA_MOUNT/grafana/data"
mkdir -p "$DATA_MOUNT/grafana/provisioning/datasources"

# Важно: официальные образы Prometheus и Grafana работают под UID 65534 (nobody)
chown -R 65534:65534 "$DATA_MOUNT/prometheus/data"
chown -R 65534:65534 "$DATA_MOUNT/grafana/data"

# --- docker-compose.yml ---
cat > "$DATA_MOUNT/docker-compose.yml" <<'EOF'
version: '3.8'
services:
  prometheus:
    image: prom/prometheus:v2.50.1
    container_name: prometheus
    restart: always
    ports:
      - "9090:9090"
    volumes:
      - ./prometheus/data:/prometheus
      - ./prometheus/prometheus.yml:/etc/prometheus/prometheus.yml:ro
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.path=/prometheus'
    ulimits:
      nofile:
        soft: 65536
        hard: 65536

  grafana:
    image: grafana/grafana:10.2.0
    container_name: grafana
    restart: always
    ports:
      - "3000:3000"
    volumes:
      - ./grafana/data:/var/lib/grafana
      - ./grafana/provisioning:/etc/grafana/provisioning
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin
      - GF_USERS_ALLOW_SIGN_UP=false
    depends_on:
      - prometheus
EOF

# --- prometheus.yml ---
cat > "$DATA_MOUNT/prometheus/prometheus.yml" <<EOF
global:
  scrape_interval: 15s
scrape_configs:
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']
EOF

# --- provisioning datasource для Grafana ---
cat > "$DATA_MOUNT/grafana/provisioning/datasources/datasource.yml" <<EOF
apiVersion: 1
datasources:
  - name: Prometheus
    type: prometheus
    access: proxy
    url: http://localhost:9090
    isDefault: true
EOF

cd "$DATA_MOUNT"
docker compose up -d

ufw allow 9090/tcp
ufw allow 3000/tcp

echo "Done. Prometheus: http://$(curl -s 169.254.169.254/metadata/v1/instance/network-interfaces/0/public-ip-address):9090, Grafana: ...:3000"