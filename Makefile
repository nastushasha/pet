.PHONY: db-up db-down db-logs

# Поднять только PostgreSQL (для Laravel на хосте: DB_HOST=127.0.0.1 в .env)
db-up:
	docker compose up -d postgres
	@echo "Postgres на 127.0.0.1:5432 (если не меняли POSTGRES_PORT). user/db: vacancy"

db-down:
	docker compose stop postgres

db-logs:
	docker compose logs -f postgres
