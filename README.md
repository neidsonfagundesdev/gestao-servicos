# Gestão de Serviços — API

API REST desenvolvida em **Laravel** para gerenciamento de clientes, serviços e agendamentos.

O projeto possui autenticação por token, perfis de acesso para administradores e funcionários, validações de regras de negócio, prevenção de conflitos de horários e uma suíte de testes automatizados.

A aplicação faz parte de um projeto Full Stack composto por:

- **Backend:** Laravel + MySQL
- **Frontend:** React + Vite
- **Banco de dados em produção:** MySQL na Aiven
- **Deploy:** Render
- **Autenticação:** Laravel Sanctum

---

## Aplicação em produção

**Frontend**

https://gestao-servicos-front.onrender.com

**API**

https://gestao-servicos-c7t4.onrender.com

**Repositório do frontend**

https://github.com/neidsonfagundesdev/gestao-servicos-front

---

## Tecnologias utilizadas

- PHP 8.5
- Laravel 13
- Laravel Sanctum
- MySQL
- PHPUnit
- Docker
- Apache
- Composer
- Aiven
- Render

---

## Funcionalidades

### Autenticação

O sistema possui autenticação baseada em **Bearer Token** utilizando Laravel Sanctum.

O fluxo funciona da seguinte forma:

1. o usuário envia e-mail e senha;
2. a API valida as credenciais;
3. um token é gerado;
4. o token é enviado nas próximas requisições através do header `Authorization`;
5. rotas protegidas verificam o token antes de permitir o acesso.

Os tokens possuem tempo de expiração configurável.

Por padrão:

```env
SANCTUM_EXPIRATION=60
```

Ou seja, o token expira após **60 minutos**.

O endpoint de login também possui proteção contra excesso de tentativas:

```text
5 tentativas por minuto
```

Após atingir o limite, a API retorna HTTP `429 Too Many Requests`.

---

## Perfis de acesso

Existem dois perfis de usuário:

### Administrador

Pode:

- visualizar clientes;
- cadastrar clientes;
- editar clientes;
- excluir clientes;
- visualizar serviços;
- cadastrar serviços;
- editar serviços;
- excluir serviços;
- visualizar agendamentos;
- cadastrar agendamentos;
- editar qualquer agendamento;
- excluir agendamentos.

### Funcionário

Pode:

- visualizar clientes;
- cadastrar clientes;
- editar clientes;
- visualizar serviços;
- visualizar agendamentos;
- cadastrar agendamentos;
- editar apenas os próprios agendamentos.

Funcionários não podem excluir clientes, serviços ou agendamentos.

As permissões administrativas são protegidas por middleware.

---

## Regras de negócio dos agendamentos

O sistema possui validações para evitar inconsistências na agenda.

Entre as principais regras estão:

- não é permitido criar agendamentos em datas passadas;
- serviços inativos não podem ser utilizados em novos agendamentos;
- um funcionário não pode possuir dois agendamentos conflitantes;
- a duração do serviço é considerada ao verificar conflitos de horário;
- um funcionário não pode alterar o agendamento pertencente a outro funcionário;
- o `user_id` de um agendamento não pode ser alterado através da atualização;
- agendamentos cancelados deixam de bloquear o horário;
- dois agendamentos ativos não podem ocupar exatamente o mesmo horário para o mesmo funcionário.

Status disponíveis:

```text
agendado
confirmado
em_atendimento
concluido
cancelado
```

---

## Proteção de integridade dos dados

Clientes e serviços que já possuem agendamentos vinculados não podem ser excluídos.

Nesses casos, a API retorna:

```text
409 Conflict
```

Isso evita a remoção de registros que ainda são utilizados por outros dados do sistema.

---

## Endpoints

A URL base da API é:

```text
/api
```

### Autenticação

| Método | Endpoint | Autenticação | Descrição |
|---|---|---|---|
| POST | `/api/login` | Não | Realiza login |
| GET | `/api/me` | Sim | Retorna o usuário autenticado |
| POST | `/api/logout` | Sim | Revoga o token atual |

### Dashboard

| Método | Endpoint | Autenticação | Descrição |
|---|---|---|---|
| GET | `/api/dashboard` | Sim | Retorna dados do dashboard |

### Clientes

| Método | Endpoint | Permissão | Descrição |
|---|---|---|---|
| GET | `/api/clientes` | Autenticado | Lista clientes |
| GET | `/api/clientes/{id}` | Autenticado | Busca cliente |
| POST | `/api/clientes` | Autenticado | Cria cliente |
| PATCH | `/api/clientes/{id}` | Autenticado | Atualiza cliente |
| DELETE | `/api/clientes/{id}` | Admin | Exclui cliente |

### Serviços

| Método | Endpoint | Permissão | Descrição |
|---|---|---|---|
| GET | `/api/servicos` | Autenticado | Lista serviços |
| GET | `/api/servicos/{id}` | Autenticado | Busca serviço |
| POST | `/api/servicos` | Admin | Cria serviço |
| PATCH | `/api/servicos/{id}` | Admin | Atualiza serviço |
| DELETE | `/api/servicos/{id}` | Admin | Exclui serviço |

### Agendamentos

| Método | Endpoint | Permissão | Descrição |
|---|---|---|---|
| GET | `/api/agendamentos` | Autenticado | Lista agendamentos |
| GET | `/api/agendamentos/{id}` | Autenticado | Busca agendamento |
| POST | `/api/agendamentos` | Autenticado | Cria agendamento |
| PATCH | `/api/agendamentos/{id}` | Autenticado | Atualiza agendamento |
| DELETE | `/api/agendamentos/{id}` | Admin | Exclui agendamento |

---

## Exemplo de autenticação

### Requisição

```http
POST /api/login
Content-Type: application/json
Accept: application/json
```

```json
{
  "email": "usuario@exemplo.com",
  "password": "senha"
}
```

### Requisição autenticada

Após o login, envie o token:

```http
Authorization: Bearer SEU_TOKEN
Accept: application/json
```

---

## Usuários de demonstração

O projeto possui um seeder para criação e atualização dos usuários utilizados na demonstração da aplicação.

As senhas **não ficam armazenadas no código-fonte**.

Elas são configuradas através das variáveis de ambiente:

```env
DEMO_ADMIN_PASSWORD=
DEMO_FUNCIONARIO_PASSWORD=
```

O seeder utiliza `updateOrCreate`, permitindo atualizar os usuários de demonstração sem criar registros duplicados.

---

## Executando o projeto localmente

### Pré-requisitos

Tenha instalado:

- PHP 8.3 ou superior
- Composer
- MySQL
- Git

Clone o repositório:

```bash
git clone https://github.com/neidsonfagundesdev/gestao-servicos.git
```

Entre na pasta:

```bash
cd gestao-servicos
```

Instale as dependências:

```bash
composer install
```

Crie o arquivo `.env`:

### Windows PowerShell

```powershell
Copy-Item .env.example .env
```

### Linux/macOS

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

---

## Configuração do banco de dados

Crie um banco MySQL:

```sql
CREATE DATABASE gestao_servicos;
```

Configure o `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestao_servicos
DB_USERNAME=root
DB_PASSWORD=
```

Depois execute:

```bash
php artisan migrate
```

Caso queira criar os usuários de demonstração, configure:

```env
DEMO_ADMIN_PASSWORD=sua_senha
DEMO_FUNCIONARIO_PASSWORD=sua_senha
```

E execute:

```bash
php artisan db:seed
```

---

## Iniciando a API

Execute:

```bash
php artisan serve
```

Por padrão, a aplicação ficará disponível em:

```text
http://127.0.0.1:8000
```

---

## Testes automatizados

O projeto possui testes de feature para autenticação, permissões e regras de negócio.

Execute:

```bash
php artisan test
```

Estado atual da suíte:

```text
25 testes passando
```

Entre os cenários testados estão:

- login com credenciais válidas;
- login com senha incorreta;
- bloqueio após excesso de tentativas;
- rejeição de tokens expirados;
- acesso de usuários não autenticados;
- permissões de administrador e funcionário;
- criação de agendamentos;
- bloqueio de datas passadas;
- bloqueio de serviços inativos;
- conflitos de horários;
- reutilização de horários cancelados;
- proteção contra alteração do funcionário do agendamento;
- funcionário impedido de editar agendamento de outro funcionário;
- administrador autorizado a editar outros agendamentos;
- exclusão protegida de clientes e serviços vinculados a agendamentos.

Os testes utilizam banco SQLite em memória durante a execução automatizada.

As migrations específicas utilizadas em produção também foram validadas localmente com MySQL.

---

## Segurança

Algumas medidas implementadas no projeto:

- autenticação com Laravel Sanctum;
- Bearer Tokens;
- expiração de tokens;
- rate limiting no login;
- middleware de autorização por perfil;
- validação dos dados recebidos pela API;
- controle de acesso às operações administrativas;
- senhas armazenadas utilizando hash;
- credenciais de produção fora do repositório;
- certificado SSL do banco armazenado como Secret File no ambiente de produção.

---

## Deploy

### Backend

O backend está hospedado no Render através de um container Docker.

Durante a inicialização do container são executados:

```text
config:cache
migrate --force
db:seed --force
Apache
```

### Banco de dados

O banco MySQL de produção está hospedado na Aiven.

A conexão utiliza SSL, com o certificado disponibilizado ao container através de Secret File.

Exemplo:

```env
MYSQL_ATTR_SSL_CA=/etc/secrets/ca.pem
```

---

## Docker

A imagem de produção utiliza:

```dockerfile
php:8.5-apache
```

O container instala as extensões necessárias ao Laravel, incluindo:

- `pdo_mysql`
- `mbstring`
- `intl`
- `zip`
- `bcmath`

O Apache utiliza a pasta:

```text
/public
```

como Document Root da aplicação.

---

## Estrutura do projeto

```text
app/
├── Http/
│   ├── Controllers/
│   └── Middleware/
├── Models/

config/

database/
├── factories/
├── migrations/
└── seeders/

routes/
└── api.php

tests/
└── Feature/

Dockerfile
composer.json
```

---

## Principais conceitos praticados

Este projeto foi desenvolvido também com o objetivo de aplicar conceitos importantes de desenvolvimento backend, entre eles:

- APIs REST;
- autenticação;
- autorização;
- middleware;
- migrations;
- seeders;
- relacionamentos entre tabelas;
- validação de dados;
- regras de negócio;
- códigos de status HTTP;
- tratamento de conflitos;
- testes automatizados;
- variáveis de ambiente;
- Docker;
- banco de dados em nuvem;
- deploy de aplicações.

---

## Frontend

A interface do sistema foi desenvolvida separadamente utilizando React e Vite.

Repositório:

https://github.com/neidsonfagundesdev/gestao-servicos-front

Aplicação:

https://gestao-servicos-front.onrender.com

---

## Autor

**Neidson Fagundes**

GitHub:

https://github.com/neidsonfagundesdev