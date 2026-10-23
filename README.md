# Catàleg de Serveis

Aplicació web per gestionar un catàleg de serveis d’un institut: l’administració publica serveis i les empreses els sol·liciten. Cada canvi d’estat (acceptada, rebutjada, realitzada, validada) es registra també a un smart contract a la **devnet de MultiversX**, de manera que la traçabilitat quedi a la blockchain.

## Com funciona

- **Admin**: crea i edita serveis, accepta o rebutja sol·licituds i consulta la traçabilitat on-chain.
- **Empresa**: es registra, veu el catàleg i demana serveis.
- **Backend**: PHP + MariaDB (usuaris, serveis i sol·licituds).
- **Blockchain**: contracte Rust (`ServiceRegistry`) que desa esdeveniments (`recordEvent`) i permet consultar l’historial d’una wallet.

## Desplegament

Instruccions pas a pas per muntar el projecte en un VPS (Nginx, PHP, MariaDB, wallet i contracte):

[DEPLOY.md](DEPLOY.md)
