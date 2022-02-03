#!/bin/bash

folders=(.github Block Controller etc Helper Model Observer Setup Test view )
files=(.gitignore composer.json LICENSE README.md registration.php)

for files in "${folders[@]}"; do
    rm -r "$files"
done
for file in "${files[@]}"; do
    rm "$file"
done