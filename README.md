# Flux Payments - Sistema de Pagamentos

Sistema de pagamentos simplificado desenvolvido em PHP usando Slim Framework, seguindo os princípios de Clean Architecture e Domain-Driven Design (DDD).

## 📋 Funcionalidades

### Tipos de Usuário
- **Usuários Comuns**: Podem enviar e receber transferências
- **Lojistas/Comerciantes**: Apenas recebem transferências, não podem enviar

### Recursos Implementados
- ✅ Cadastro de usuários (comuns e lojistas)
- ✅ Carteiras digitais para todos os usuários
- ✅ Sistema de transferências entre usuários
- ✅ Validação de saldo antes das transferências
- ✅ Autorização externa para transferências
- ✅ Sistema de notificações
- ✅ Transações atômicas com rollback
- ✅ API RESTful completa

### Validações e Regras de Negócio
- CPF/CNPJ e e-mail únicos no sistema
- Lojistas não podem enviar transferências
- Validação de saldo suficiente antes da transferência
- Autorização externa obrigatória (mock API)
- Notificações resilientes com retry

## 🏗️ Arquitetura

O projeto segue uma arquitetura limpa (Clean Architecture) com separação de responsabilidades:

```
src/
├── Application/           # Camada de Aplicação
│   ├── Actions/          # Controladores/Endpoints
│   ├── DTO/              # Data Transfer Objects
│   ├── Handlers/         # Manipuladores de eventos
│   └── Middleware/       # Middlewares da aplicação
├── Domain/               # Camada de Domínio
│   ├── User/             # Agregado de Usuário
│   ├── Wallet/           # Agregado de Carteira
│   ├── Transaction/      # Agregado de Transação
│   └── Exceptions/       # Exceções de domínio
└── Infrastructure/       # Camada de Infraestrutura
    ├── Database/         # Repositórios e conexão
    └── ExternalServices/ # Serviços externos
```

## 🚀 Rotas da API

### Usuários
- `POST /api/v1/users` - Cadastro de usuário

### Carteiras
- `GET /api/v1/wallets/{user_id}/balance` - Consultar saldo

### Transferências
- `POST /api/v1/transfer` - Executar transferência

## 🐳 Configuração com Docker

### Pré-requisitos
- Docker
- Docker Compose

### Primeira Configuração

1. **Clone o projeto** (se aplicável):
```bash
git clone <repository-url>
cd flux-payments
```

2. **Configure as variáveis de ambiente**:
```bash
cp .env.example .env
```

Edite o arquivo `.env` com suas configurações:
```bash
# Configurações do Banco de Dados
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=flux_payments
DB_USERNAME=flux_user
DB_PASSWORD=flux_password
DB_ROOT_PASSWORD=root_password

# URLs das APIs Externas (usando as APIs mock fornecidas)
AUTHORIZER_API_URL=https://util.devi.tools/api/v2/authorize
NOTIFICATION_API_URL=https://util.devi.tools/api/v1/notify

# Configurações da Aplicação
APP_ENV=development
APP_DEBUG=true

# Docker Configuration
APP_PORT=8080
```

3. **Inicie os containers**:
```bash
docker-compose up --build
```

4. **Instale as dependências PHP** (via Docker):
```bash
# Encontre o ID do container PHP
docker ps

# Execute o bash no container (substitua CONTAINER_ID pelo ID real)
docker exec -it flux-payments-app bash

# Dentro do container, instale as dependências
composer install
```

Ou em uma linha:
```bash
docker exec -it flux-payments-app composer install
```

### Comandos Úteis do Docker

```bash
# Visualizar l
# Executar testes
docker exec -it flux-payments-app composer test

```

### Acesso à Aplicação

- **API**: http://localhost:8080
- **Banco de dados**: localhost:3306

## 📝 Exemplos de Uso da API

### 1. Cadastrar Usuário Comum
```bash
curl -X POST http://localhost:8080/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "João Silva",
    "cpf_cnpj": "12345678901",
    "email": "joao@email.com",
    "password": "senha123",
    "type": "COMMON"
  }'
```

### 2. Cadastrar Lojista
```bash
curl -X POST http://localhost:8080/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Loja ABC Ltda",
    "cpf_cnpj": "12345678000190",
    "email": "contato@lojaabc.com",
    "password": "senha123",
    "type": "MERCHANT"
  }'
```

### 3. Consultar Saldo
```bash
curl -X GET http://localhost:8080/api/v1/wallets/1/balance
```

### 4. Fazer Transferência
```bash
curl -X POST http://localhost:8080/api/v1/transfer \
  -H "Content-Type: application/json" \
  -d '{
    "payer_id": 1,
    "payee_id": 2,
    "amount": 100.50
  }'
```

## 🔧 Desenvolvimento

### Executar Testes
```bash
docker exec -it flux-payments-app composer test
```

### Análise de Código
```bash
# PHPStan (análise estática)
docker exec -it flux-payments-app composer phpstan

# Code Sniffer (padrões de código)
docker exec -it flux-payments-app composer phpcs
```

### Estrutura do Banco de Dados

O sistema utiliza MariaDB com as seguintes tabelas principais:

- `users` - Dados dos usuários
- `wallets` - Carteiras digitais
- `transactions` - Histórico de transações

## 🔒 Segurança

- Senhas são hasheadas usando password_hash() do PHP
- Validação de entrada em todas as rotas
- Transações atômicas para consistência de dados
- Logs de auditoria para todas as operações

## 🚨 Tratamento de Erros

O sistema implementa tratamento robusto de erros:

- Validação de entrada com mensagens claras
- Rollback automático em caso de falha na transação
- Logs detalhados para debugging
- Códigos de status HTTP apropriados

## 📊 Monitoramento

- Logs estruturados em `logs/app.log`
- Métricas de performance
- Rastreamento de transações

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

**Desenvolvido usando PHP, Slim Framework e Docker**
