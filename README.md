<img src="https://bon-core-files.ams3.digitaloceanspaces.com/logo-name-white.png" alt="Logo" width="200" />

### Ingest - CCV
This repository is responsible for the integration with CCV Shop and communicates with the BFF - Ingest. 

### Transformers
| Code           | Example | Description |
|----------------|----|----|
| `gid:`         |`gid:shipment:id`| Generates the shipment GID based on the businessUUID, object type and the resource ID. |
| `bon_default:` |`bon_default:currency`| Grabs the currency from the defaults stored |
| `&#124;`       |`addressBillingStreet&#124;addressBillingNumber`| Concatenates a multiple values in a string seperated by a space |
|`.`|`customer.billingaddress.full_name`| Goes a level deeper in the array | 
|`BON_BUSINESSUUID`|`BON_BUSINESSUUID`| Fills in the business UUID | 
|`sub:`|`sub:total_price:total_tax`| Substracts values. First value mentiond is the amount from which the next values will be subtracted | 
|`dtax:`|`dtax:price_without_discount:tax`| Deducts the tax from a value |
|`if_empty:`|`if_empty:ddressBillingStreet:addressBillingNumber`| Finds the first value that has characters. |




### Implemented Features
- `n/a`

### Known Limitations
- If an order has been set to shipped and then is status changes to non-shipped. This is not incorporated. 
- CCV Orders always have a single shipment (CCV Limitation).
