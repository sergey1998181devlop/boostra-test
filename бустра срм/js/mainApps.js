class mainApps {

    // отправить get запрос
    async sendGet(url) {
        let result = await fetch(url);
        let res = await result.json();
        if (res) {
            return res;
        }
        return false;
    }

    async sendPost(url, data) {
        let formData = new FormData();
        formData.append('data', JSON.stringify(data));
        let res = $.ajax({
            url: url,
            type: "POST",
            data: formData,
            async: true,
            cache: false,
            contentType: false,
            processData: false
        });
        return res;
    }

    // получить все блоки с классом
    getAllBlocks(className) {
        return document.getElementsByClassName(className);
    }

    // очистить блок
    clearBlock(id) {
        let block = this.getBlock(id);
        block.innerHTML = '';
        return block;
    }

    // очистить поле ввода
    clearInput(id) {
        let block = this.getBlock(id);
        if (block) {
            block.value = '';
            return block;
        }
    }

    // вставить текст в поле ввода
    setValue(id, text) {
        let block = this.getBlock(id);
        if (block) {
            block.value = text;
            return block;
        }
        return false;
    }

    // вставить текст в блок
    setTextBlock(id, text) {
        let block = this.getBlock(id);
        if (block) {
            block.innerHTML = text;
            return block;
        }
        return false;
    }

    // получить текст из поля ввода
    getValue(id) {
        let block = this.getBlock(id);
        return block.value;
    }

    // получить текст из блока
    getTextBlock(id) {
        let block = this.getBlock(id);
        return block.textContent;
    }

    // показать блок
    openBlock(id) {
        let block = this.getBlock(id);
        if (block) {
            block.style.display = 'block';
            return block;
        } else {
            return false;
        }
    }

    // скрыть блок
    closeBlock(id) {
        let block = this.getBlock(id);
        if (block) {
            block.style.display = 'none';
            return block;
        } else {
            return false;
        }
    }

    // получить блок
    getBlock(id) {
        let block = document.getElementById(id);
        if (block) {
            return block;
        }
        console.log('Блок с id ' + id + ' на странице не найден');
        return false;
    }

    // получить количество от текущейго времени до указанной даты
    getCountDays(searchDate) {
        let currentDate = new Date();
        let date = new Date(searchDate);
        return Number(Math.floor((date.getTime() - currentDate.getTime()) / (1000 * 60 * 60 * 24)));
    }
}