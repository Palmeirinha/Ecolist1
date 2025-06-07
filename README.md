# EcoList - Sistema de Gerenciamento de Alimentos

## Sobre o Projeto
EcoList é um sistema de gerenciamento de alimentos que ajuda a controlar validades, evitar desperdício e sugerir receitas baseadas nos ingredientes disponíveis.

## Funcionalidades Principais
- Cadastro e gerenciamento de alimentos
- Controle de validade com alertas
- Sugestões de receitas
- Dashboard com estatísticas
- Sistema de categorização
- Controle de acesso e permissões

## Requisitos
- PHP 8.1 ou superior
- Composer
- Node.js 16 ou superior
- MySQL 5.7 ou superior
- Extensões PHP necessárias:
  - BCMath
  - Ctype
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

## Instalação

1. Clone o repositório:
```bash
git clone [url-do-repositorio]
cd ecolist
```

2. Instale as dependências PHP:
```bash
composer install
```

3. Instale as dependências JavaScript:
```bash
npm install
```

4. Configure o ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure o banco de dados no arquivo .env:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecolist
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

6. Execute as migrações:
```bash
php artisan migrate --seed
```

7. Compile os assets:
```bash
npm run build
```

8. Inicie o servidor:
```bash
php artisan serve
```

## Desenvolvimento

Para ambiente de desenvolvimento, use:
```bash
npm run dev
```

## Testes
Execute os testes com:
```bash
php artisan test
```

## Cache
Para otimizar a performance:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Manutenção
Para limpar o cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Estrutura do Projeto
- `app/` - Código principal da aplicação
  - `Http/Controllers/` - Controladores
  - `Models/` - Modelos Eloquent
  - `Services/` - Serviços da aplicação
- `config/` - Arquivos de configuração
- `database/` - Migrações e seeders
- `resources/` - Views, assets e arquivos de linguagem
- `routes/` - Definições de rotas
- `tests/` - Testes automatizados

## Contribuição
1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença
Este projeto está licenciado sob a licença MIT - veja o arquivo LICENSE.md para detalhes.

## Suporte
Em caso de dúvidas ou problemas, abra uma issue no repositório.
