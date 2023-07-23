# avantroczTest -- Test Job for parsing foxentry products and prices

## Czech version of the test. For the English version, see below.
Vytvořte parser, který z konkrétní stránky vytáhne požadovaná data a zaindexuje je do ElasticSearch indexu. 
Ze stránky //foxentry.com/cs/cenik-api z části "Všechny funkce pohromadě" získejte a zaindexujte údaje následovně:
- Získejte informace o všech nabízených službách (1 řádek = 1 služba) 
- U každé služby získejte její název, popis (zobrazuje se po najetí na ikonu 🛈) a ceny. 
- Vytvořte ElasticSearch index a zaindexujte tam každou službu jako samostatný dokument 
- Zdrojové kódy nahrajte do GIT repozitáře a pamatujte, že kód musí být čitelný pro ostatní programátory.

## English version
Create a parser that pulls the required data from a specific page and indexes it into the ElasticSearch index.
From the //foxentry.com/cs/cenik-api page from the "Všechny funkce pohromadě" (All features in one place) section,
retrieve and index the data as follows:
- Get information about all services offered (1 row = 1 service)
- For each service, get its name, description (displayed by clicking on the 🛈 icon) and prices.
- Create an ElasticSearch index and index each service there as a separate document
- Upload the source code to the GIT repository, remembering that the code must be readable by other programmers.

## Install:

`composer install`

`php -S localhost:8000`

### Install Elasticsearch
Follow the official Elasticsearch installation guide to install Elasticsearch on your system:
https://www.elastic.co/guide/en/elasticsearch/reference/current/install-elasticsearch.html

By default, Elasticsearch is configured to allow automatic index creation, and no additional steps are required. However, if you have disabled automatic index creation in Elasticsearch, you must configure action.auto_create_index in elasticsearch.yml to allow the commercial features to create the following indices:

`action.auto_create_index: .monitoring*,.watches,.triggered_watches,.watcher-history*,.ml*`

Make sure you have Elasticsearch running on the default address and port (localhost:9200).

## Run
Start with http://localhost:8000
