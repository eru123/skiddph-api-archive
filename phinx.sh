#!/bin/bash

# get all arguments
args=("$@")

# get working directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# run phinx
php $DIR/vendor/bin/phinx $args
