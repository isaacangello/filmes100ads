# Scripts do Minerador CoinImp

Este diretório deve conter os scripts do minerador CoinImp para que a mineração funcione.

## Como instalar

1. Acesse o painel do [CoinImp](https://coinimp.com) e faça login.
2. No painel do seu site, na seção **Config** ou **Settings**, encontre o link para baixar os scripts do minerador.
3. O CoinImp recomenda o seguinte código:

```html
<script src="http://<your-domain>/<path-to-script>/jiB0.php?f=RqH3.js"></script>
<script>
    var _client = new Client.Anonymous('SUA_SITE_KEY', {
        throttle: 0, c: 'w'
    });
    _client.start();
    _client.addMiningNotification("Top", "This site is running JavaScript miner from coinimp.com. If it bothers you, you can stop it.", "#cccccc", 40, "#3d3d3d");
</script>
```

4. Baixe os arquivos `jiB0.php` e `RqH3.js` fornecidos pelo CoinImp.
5. Coloque ambos os arquivos neste diretório (`public/miner/`).

## Configuração no .env

Após colocar os scripts, configure no `.env`:

```
MINER_ENABLED=true
MINER_SITE_KEY=d792b141f22cd4109c6bd5965870272519b9c297191194d4d0f5d299dc9d279c
MINER_SCRIPT_PATH=miner/jiB0.php?f=RqH3.js
```

## Verificação

Após instalar os scripts:
- O card do minerador no dashboard admin (`/adm`) mostrará "Scripts presentes" quando os arquivos estiverem no lugar.
- No navegador, após aceitar o banner de cookies, o minerador deve iniciar e o painel do CoinImp deve começar a registrar hashes.

## Importante

- Os scripts `jiB0.php` e `RqH3.js` são fornecidos exclusivamente pelo CoinImp — não os crie manualmente.
- Sem esses arquivos, o minerador não funciona, mesmo com `MINER_ENABLED=true`.
- O minerador só é carregado após o usuário aceitar o banner de consentimento (`localStorage.fda_consent === '1'`).
