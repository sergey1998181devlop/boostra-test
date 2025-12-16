#!/bin/bash
DEPLOY_PATH=$1
getVal=$(ls $DEPLOY_PATH | grep -v files | grep -v logs | grep -v compiled )

for j in $getVal
do
	chown "$USER":www-data $DEPLOY_PATH/$j -R
        
done
