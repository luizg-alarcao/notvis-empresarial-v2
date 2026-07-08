# Deploy do NOTVIS no Railway

## Caminho recomendado

Use Railway com GitHub e banco MySQL. Esse caminho fica mais parecido com o Laragon e reduz risco na apresentacao.

## Servicos no Railway

Crie dois servicos dentro do mesmo projeto:

1. App do Laravel, puxando este repositorio do GitHub.
2. Banco MySQL.

## Configuracao do app

No servico do Laravel, configure:

- Build Command: `npm run build`
- Pre-Deploy Command: `chmod +x ./railway/init-app.sh && sh ./railway/init-app.sh`

Depois crie um dominio publico em:

- Settings
- Networking
- Generate Domain

## Variaveis

No servico do Laravel, abra `Variables` e use como base o arquivo `.env.railway.example`.

Preencha `APP_KEY` com uma chave Laravel valida e troque `APP_URL` pelo link gerado pelo Railway.

Para o banco MySQL, use as variaveis do proprio Railway:

- `DB_HOST=${{MySQL.MYSQLHOST}}`
- `DB_PORT=${{MySQL.MYSQLPORT}}`
- `DB_DATABASE=${{MySQL.MYSQLDATABASE}}`
- `DB_USERNAME=${{MySQL.MYSQLUSER}}`
- `DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}`

## Primeiro acesso

Depois que o deploy terminar:

1. Abra o link publico do Railway.
2. Crie o primeiro usuario administrador.
3. Cadastre empresa, funcionarios, clientes e produtos de demonstracao.
4. Teste abertura, fechamento e relatorios antes da apresentacao.

## Plano B

Mantenha o Laragon pronto na maquina local para apresentacao caso a internet ou a hospedagem falhe.
