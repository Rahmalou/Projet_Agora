#!/bin/bash

echo "Setting up the symfony project"
php composer.phar install
./updateSchema.sh
php bin/console doctrine:database:import Data/GameInfo.sql
php bin/console doctrine:database:import Data/admin.sql
php bin/console doctrine:database:import Data/SplendorCards.sql
./clearCache.sh
echo "Done !"
