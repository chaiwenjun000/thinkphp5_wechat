#!/bin/bash
commit=$(date +"%Y-%m-%d %H:%M:%S");
git add -A;
git commit -m "$commit";
git push origin master;
