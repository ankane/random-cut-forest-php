# Random Cut Forest PHP

[Random Cut Forest](https://github.com/aws/random-cut-forest-by-aws) (RCF) anomaly detection for PHP

[![Build Status](https://github.com/ankane/random-cut-forest-php/workflows/build/badge.svg?branch=master)](https://github.com/ankane/random-cut-forest-php/actions)

## Installation

Run:

```sh
composer require ankane/rcf
```

And download the shared library:

```sh
composer exec -- php -r "require 'vendor/autoload.php'; Rcf\Vendor::check(true);"
```

## Getting Started

Create a forest with 3 dimensions

```php
$forest = new Rcf\Forest(3);
```

Score a point

```php
$forest->score([1.0, 2.0, 3.0]);
```

Update with a point

```php
$forest->update([1.0, 2.0, 3.0]);
```

## Example

```php
$forest = new Rcf\Forest(3);

for ($i = 0; $i < 200; $i++) {
    $point = [];
    $point[0] = mt_rand() / mt_getrandmax();
    $point[1] = mt_rand() / mt_getrandmax();
    $point[2] = mt_rand() / mt_getrandmax();

    // make the second to last point an anomaly
    if ($i == 198) {
        $point[1] = 2;
    }

    $score = $forest->score($point);
    echo "point = $i, score = $score\n";
    $forest->update($point);
}
```

## Parameters

Set parameters

```php
new Rcf\Forest(
    $dimensions,
    shingleSize: 1,         // shingle size to use
    sampleSize: 256,        // points to keep in sample for each tree
    numberOfTrees: 100,     // number of trees to use in the forest
    randomSeed: 42,         // random seed to use
    parallel: false         // enable parallel execution
)
```

## References

- [Robust Random Cut Forest Based Anomaly Detection On Streams](https://proceedings.mlr.press/v48/guha16.pdf)

## History

View the [changelog](CHANGELOG.md)

## Contributing

Everyone is encouraged to help improve this project. Here are a few ways you can help:

- [Report bugs](https://github.com/ankane/random-cut-forest-php/issues)
- Fix bugs and [submit pull requests](https://github.com/ankane/random-cut-forest-php/pulls)
- Write, clarify, or fix documentation
- Suggest or add new features

To get started with development:

```sh
git clone https://github.com/ankane/random-cut-forest-php.git
cd random-cut-forest-php
composer install
composer test
```
