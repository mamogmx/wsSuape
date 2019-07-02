import requests
from datetime import datetime
import hashlib
import json

url = "https://servizi.sardegnasuap.it/suape-gestione-praticaFE/services/protocollazione/getListaEndoProcedimenti"
#url = "https://servizi.sardegnasuap.it/suape-gestione-praticaFE/services/protocollazione/getListaPratiche"
#url = "https://195.130.213.151/suape-gestione-praticaFE/services/protocollazione/getListaEndoProcedimenti"
#url = "https://suape-pub.dedagroup.it/suape-gestione-praticaFE/services/protocollazione/getListaEndoProcedimenti"
#url = "https://servizi.sardegnasuap.it/suape-gestione-praticaFE/services/protocollazione/getListaSettori"
#url = "https://servizi.sardegnasuap.it/suape-gestione-praticaFE/services/integrazioneBackOffice/getListaPratiche"
#url = "https://servizi.sardegnasuap.it/suape-gestione-praticaFE/services/protocollazione/elencoPratiche"
d = datetime.now().strftime("%Y/%m/%d")
key = "edrfikfgvbekgvb"
hash_string = "%s%s" %(key,d)
AuAuth = hashlib.sha256(hash_string.encode()).hexdigest()
key = "edrfikfgvbekgvb"
sportello = "dc74d8ee-7189-40ae-aac7-c428e329f08f"
ente = "6b7a4aa6-3dd5-4505-8ac6-02ea23a04a49"
#ente = "EC1410"
h = dict()
h['AU-auth'] = "9e6fceea44f4162d3c2a65ff668d27a0faf578d98c28307e42bf4d6338319f8c"
h['idente'] = ente
h['Content-Type'] = 'application/json'
#h['Postman-Token'] = '15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
h['cache-control'] = "no-cache"
#json.dumps(h)
#res = requests.post(url)
postData=dict(notifica=False,verifica=True)
#postData=dict(DataInoltroDa='2019-05-30',HeaderAuth=h)

res = requests.post(url, data = json.dumps(postData), headers=h, verify=False)

print res.text
print h
print json.dumps(postData)
