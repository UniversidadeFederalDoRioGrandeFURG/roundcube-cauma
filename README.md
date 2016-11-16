![alt tag](https://cauma.pop-ba.rnp.br/static/img/cauma_black.png)

Plugin de integração do Roundcube com o serviço [CaUMa](https://cauma.pop-ba.rnp.br/about).

## Sobre o CaUMa

CaUMa (Catálogo de URLs Maliciosas) é um serviço gratuito e público criado pelo CERT.Bahia, que disponibiliza um meio de consulta a urls fraudulentas identificadas na Internet. O propósito desse serviço é ajudar a comunidade a se proteger das diversas fraudes que estão circulando no mundo digital.

## Pré-requisitos

- Módulo cURL ativo no PHP

Para ativar em ambiente Debian + Apache
```
sudo apt-get install php5-curl
sudo php5enmod curl
sudo service apache2 restart
```

## Como ativar o plugin

Clonar o repositório roundcube-cauma dentro da pasta plugins da instalação do roundcube. Editar o arquivo de configuração e incluir o nome da pasta na lista de plugins ativos;

```
git clone https://github.com/lisandroTSilva/roundcube-cauma.git /caminho/instalacao/roundcube/plugins/cauma
touch /var/log/cauma.log
sudo chown www-data:www-data /var/log/cauma.log
```

Em /caminho/instalacao/roundcube/config/config.inc.php
```php
$config['plugins'] = array(... , 'cauma');
$config['cauma_cache'] = 'db';
$config['cauma_cache_ttl'] = '86400';
```

## Rotacionando o log
```shell
sudo cat <<EOF > /etc/logrotate.d/cauma
/var/log/cauma.log {
  rotate 7
  daily
  missingok
  notifempty
  delaycompress
  compress
}
EOF
```

## Analisando Logs ##

Relação de URLs que foram checadas e retornadas como **NÃO FRAUDULENTAS**
```
grep -P '^[\d\-: ]+ URL ok ' /var/log/cauma.log
```

Relação de URLs que foram checadas e retornadas como **FRAUDULENTAS**
```
grep -P '^[\d\-: ]+ URL block ' /var/log/cauma.log
```

Contador dos tempos de consulta por URL
```
grep -P '^[\d\-: ]+ Tempo ' /var/log/cauma.log | awk -F' ' '{print "echo \"scale=1;"$4"/"$5"\" | bc"}' | bash | sort -n | uniq -c | sort -n
```
A primeira coluna número de ocorrencias a segunda o tempo demandado para consulta, por exemplo:
```
      1 1.0
    122 .5
```
- 122 requisições de consulta ao CaUMa foram respondidas em 0.5 segundos
- 1 requisição respondeu em 1 segundo

Contador de tempos de processamento antes de apresentar e-mail
```
grep -P '^[\d\-: ]+ Tempo ' /var/log/cauma.log | cut -d' ' -f4 | sort -n | uniq -c | sort -n
```
A primeira coluna número de ocorrencias a segunda o tempo total de processamento antes de apresentar o e-mail, por exemplo:
```
      4 10
     73 0
```
- 73 e-mails precisaram de 0 segundos para analisar suas URLs
- 4 e-mails precisaram de 10 segundos para analisar suas URLs

Normalmente o tempo elevado de processamento é decorrente de um grande número de URLs no e-mail
