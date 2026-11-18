# University Course Management System

A full-stack university course-management project built around PHP, SQL and a containerised web-server configuration.

## Features

The project describes role-based authentication, subject and course management, enquiry tracking, search and an administrator dashboard.

## Repository contents

- `websites/`: website source and related files
- `database.sql`: database script at the repository root
- `websites/database.sql`: additional database export
- `docker-compose.yml`: local container orchestration
- `PHP.Dockerfile`: PHP image configuration
- `nginx.conf`: web-server configuration
- `web programmin report.docx`: accompanying academic report

## Getting started

1. Review `docker-compose.yml` for required services, ports, environment variables and mounted volumes.
2. Check `PHP.Dockerfile` and `nginx.conf` before launching the stack.
3. Inspect the SQL scripts and import the appropriate schema into a **local development database**. Do not import both SQL files blindly: their contents may differ.
4. Run `docker compose up --build` after confirming that local credentials, volumes and ports are configured appropriately.
5. Verify application routes and authentication locally.

> The setup has not yet been independently executed as part of this documentation update. Review all credentials and database exports before public deployment.

## Portfolio improvements still needed

- [ ] Add screenshots of login, course management and the admin dashboard.
- [ ] Document exact local URL, test accounts and environment variables without publishing secrets.
- [ ] Add repeatable authentication, authorisation and database tests.
- [ ] Check access controls, password hashing, SQL query parameterisation and error handling.
- [ ] Add a CI workflow after tests run locally.
- [ ] Clarify which SQL script is authoritative.

## Project status

Academic software engineering project. No production security or availability guarantee is made.
