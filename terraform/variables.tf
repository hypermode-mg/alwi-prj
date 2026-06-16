variable "cloud_id" {
  description = "ID облака Yandex Cloud"
  type        = string
}

variable "folder_id" {
  description = "ID каталога Yandex Cloud"
  type        = string
}

variable "cloud_zone" {
  description = "Зона доступности"
  type        = string
  default     = "ru-central1-a"
}

variable "my_ip" {
  description = "Текущий IP-адрес для SG"
  type        = string
  default     = "0.0.0.0/0"
}

variable "sa_key_file" {
  description = "Путь к JSON-ключу SA"
  type        = string
  default     = ""
}

variable "ssh_public_key" {
  description = "Публичный SSH-ключ"
  type        = string
}

variable "vm_cpu" {
  description = "Количество ядер CPU"
  type        = number
  default     = 2
}

variable "vm_ram" {
  description = "Объём RAM в ГБ"
  type        = number
  default     = 4
}

variable "boot_disk_size" {
  description = "Размер загрузочного диска в ГБ"
  type        = number
  default     = 50
}

variable "data_disk_size" {
  description = "Размер persistent-диска для данных в ГБ"
  type        = number
  default     = 100
}

variable "grafana_admin_password" {
  type      = string
  sensitive = true
}
