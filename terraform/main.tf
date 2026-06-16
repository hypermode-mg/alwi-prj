terraform {
  required_providers {
    yandex = {
      source  = "yandex-cloud/yandex"
      version = ">= 0.90.0"
    }
  }
}

provider "yandex" {
  cloud_id = var.cloud_id != "" ? var.cloud_id : null
  folder_id = var.folder_id
  service_account_key_file = var.sa_key_file != "" ? var.sa_key_file : null
}

# Берём актуальный образ Ubuntu 24.04 по family
data "yandex_compute_image" "os" {
  family = "ubuntu-2404-lts"
}

# Создаём сеть и подсеть
resource "yandex_vpc_network" "monitoring_net" {
  name = "monitoring-net"
}

resource "yandex_vpc_subnet" "monitoring_subnet" {
  name           = "monitoring-subnet"
  zone           = var.cloud_zone
  network_id     = yandex_vpc_network.monitoring_net.id
  v4_cidr_blocks = ["10.0.1.0/24"]
}

# Создаём группу безопасности
resource "yandex_vpc_security_group" "monitoring_sg" {
  name        = "monitoring-sg"
  network_id  = yandex_vpc_network.monitoring_net.id
}

# Правило: SSH (22)
resource "yandex_vpc_security_group_rule" "monitoring_sg_ssh" {
  security_group_binding = yandex_vpc_security_group.monitoring_sg.id
  direction              = "ingress"
  protocol               = "TCP"
  port                   = 22
  v4_cidr_blocks         = [var.my_ip]
}

# Правило: Prometheus (9090)
resource "yandex_vpc_security_group_rule" "monitoring_sg_prometheus" {
  security_group_binding = yandex_vpc_security_group.monitoring_sg.id
  direction              = "ingress"
  protocol               = "TCP"
  port                   = 9090
  v4_cidr_blocks         = [var.my_ip]
}

# Правило: Grafana (3000)
resource "yandex_vpc_security_group_rule" "monitoring_sg_grafana" {
  security_group_binding = yandex_vpc_security_group.monitoring_sg.id
  direction              = "ingress"
  protocol               = "TCP"
  port                   = 3000
  v4_cidr_blocks         = [var.my_ip]
}

# Правило: Egress (всё наружу)
resource "yandex_vpc_security_group_rule" "monitoring_sg_egress" {
  security_group_binding = yandex_vpc_security_group.monitoring_sg.id
  direction              = "egress"
  protocol               = "ANY"
  v4_cidr_blocks         = ["0.0.0.0/0"]
}

# Создаём диск для данных мониторинга
resource "yandex_compute_disk" "data_disk" {
  name       = "monitoring-data"
  type       = "network-ssd"
  zone       = var.cloud_zone
  size       = var.data_disk_size
}

# Создаём ВМ
resource "yandex_compute_instance" "monitoring" {
  name               = "monitoring-vm"
  hostname           = "monitoring"
  zone               = var.cloud_zone
  platform_id        = "standard-v1"
  resources {
    cores  = var.vm_cpu
    memory = var.vm_ram
  }
  boot_disk {
    initialize_params {
      image_id = data.yandex_compute_image.os.id
      size = var.boot_disk_size
    }
  }
  secondary_disk {
    disk_id = yandex_compute_disk.data_disk.id
  }
  network_interface {
    subnet_id          = yandex_vpc_subnet.monitoring_subnet.id
#    nat                = true
    security_group_ids = [yandex_vpc_security_group.monitoring_sg.id]
  }

  metadata = {
    ssh-keys = "ubuntu:${var.ssh_public_key}"

    # Читаем cloud-init из файла, заменяем плейсхолдер и кладём в user-data
    "user-data" = replace(file("${path.module}/cloud-init.yaml"), "$${grafana_password_placeholder}", var.grafana_admin_password)
  }
}
