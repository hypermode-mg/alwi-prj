output "vm_public_ip" {
  value = yandex_compute_instance.monitoring.network_interface.0.nat_ip_address
}

output "prometheus_url" {
  value = "http://${yandex_compute_instance.monitoring.network_interface.0.nat_ip_address}:9090"
}

output "grafana_url" {
  value = "http://${yandex_compute_instance.monitoring.network_interface.0.nat_ip_address}:3000"
}

output "data_disk_id" {
  value = yandex_compute_disk.data_disk.id
}
