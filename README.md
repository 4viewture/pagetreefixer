# pagetreefixer

pagetreefixer is a TYPO3 commandline helper to remove orphaned pages 

## Installation

Use the package manager [composer](https://getcomposer.org/) to install pagetreefixer.

```bash
composer req kaystrobach/pagetreefixer
```

## Usage

```bash
# with typo3 console
vendor/bin/typo3cms pagetreefixer:fixorphanedpages

# without typo3 console
vendor/bin/typo3 pagetreefixer:fixorphanedpages
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[GPL2.0+](https://choosealicense.com/licenses/gpl-2.0/)
