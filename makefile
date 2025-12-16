PORT=8088
IMAGE_NAME=boostra-site

docker-create:
	docker build . -f Dockerfile -t $(IMAGE_NAME)
	docker run -d -p$(PORT):$(PORT) --name $(IMAGE_NAME) -v ~/Desktop/Boostra/boostra:/var/www/html $(IMAGE_NAME)
	echo "boostra availiable at http://localhost:$(PORT)"

up:
	docker start $(IMAGE_NAME)
	echo "boostra availiable at http://localhost:$(PORT)"

down:
	docker stop $(IMAGE_NAME)

down-force:
	docker rm --force $(IMAGE_NAME)
