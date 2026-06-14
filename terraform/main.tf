terraform {
  required_providers {
    yandex = {
      source  = "yandex-cloud/yandex"
      version = ">= 0.88"
    }
  }
}

provider "yandex" {
  folder_id = var.folder_id
}

data "yandex_compute_image" "ubuntu-2404" {
  family = "ubuntu-2404-lts"
}

resource "yandex_vpc_network" "monitoring_net" {
  name = "monitoring-net"
}

resource "yandex_vpc_subnet" "monitoring_subnet" {
  name           = "monitoring-subnet"
  zone           = var.zone
  network_id     = yandex_vpc_network.monitoring_net.id
  v4_cidr_blocks = ["10.0.1.0/24"]
}

resource "yandex_vpc_security_group" "monitoring_sg" {
  name        = "monitoring-sg"
  network_id  = yandex_vpc_network.monitoring_net.id

  rule {
    direction     = "INGRESS"
    protocol      = "TCP"
    port          = 22
    v4_cidr_blocks = ["0.0.0.0/0"]
  }
  rule {
    direction     = "INGRESS"
    protocol      = "TCP"
    port          = 9090
    v4_cidr_blocks = ["0.0.0.0/0"] # В проде лучше ограничить до вашего IP
  }
  rule {
    direction     = "INGRESS"
    protocol      = "TCP"
    port          = 3000
    v4_cidr_blocks = ["0.0.0.0/0"]
  }
  rule {
    direction     = "EGRESS"
    protocol      = "ANY"
    v4_cidr_blocks = ["0.0.0.0/0"]
  }
}

resource "yandex_compute_disk" "data_disk" {
  name       = "monitoring-data"
  type       = "network-ssd"
  zone       = var.colud_zone
  size       = var.data_disk_size
}

resource "yandex_compute_instance" "monitoring" {
  name               = "monitoring-vm"
  hostname           = "monitoring"
  platform_id        = "standard-v1"
  resources {
    cores  = var.vm_cpu
    memory = var.vm_ram
  }
  boot_disk {
    initialize_params {
      image_id = data.yandex_compute_image.ubuntu-2404.id
      size     = var.boot_disk_size
    }
  }
  secondary_disk {
    disk_id = yandex_compute_disk.data_disk.id
  }
  network_interface {
    subnet_id          = yandex_vpc_subnet.monitoring_subnet.id
    nat                = true
    nat_ip_address     = true
    security_group_ids = [yandex_vpc_security_group.monitoring_sg.id]
  }
  metadata = {
    ssh-keys = "ubuntu:${var.ssh_key_file}"
  }
  user_data = file("${path.module}/scripts/install_docker_compose.sh")
}