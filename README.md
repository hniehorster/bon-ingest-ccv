<img src="https://bon-core-files.ams3.digitaloceanspaces.com/logo-name-white.png" alt="Logo" width="200" />

### Ingest - Lightspeed eCom
This repository is responsible for the integration with Lightspeed eCom and communicates with the BFF - Ingest. 

### Implemented Features
- `n/a`

### Known Limitations
* we don't fetch all products if an order has more than 250 different products
* the `shipmentProducts` don't align with the `orderProducts` making difficult to track which products have been shipped. Causing empty `variant_gid` and `product_gid`
