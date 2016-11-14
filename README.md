Plugin de integração do Roundcube com o serviço [CaUMa](https://cauma.pop-ba.rnp.br/about).


## Sobre o CaUMa

CaUMa (Catálogo de URLs Maliciosas) é um serviço gratuito e público criado pelo CERT.Bahia, que disponibiliza um meio de consulta a urls fraudulentas identificadas na Internet. O propósito desse serviço é ajudar a comunidade a se proteger das diversas fraudes que estão circulando no mundo digital.

## Como ativar o plugin

Clonar o repositório roundcube-cauma dentro da pasta plugins da instalação do roundcube. Editar o arquivo de configuração e incluir o nome da pasta na lista de plugins ativos;

```
git clone https://git.furg.br/lisandrotsilva/roundcube-cauma.git cauma
```

Em roundcubemail\config\config.inc.php

```
$config['plugins'] = array(... , 'cauma');
```