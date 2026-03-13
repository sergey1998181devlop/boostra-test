

class ViewImage extends mainApps {

    imageBlock = 'ViewImage';
    alias = '';
    _block;

    fullImg(img) {
        this._block = this.getBlock(this.imageBlock);
        let fullImage = '<img onclick="' + this.alias + '.closeImg();" src="' + img.src + '"/>';
        let padding = Math.ceil(img.width * 2.5);
        this._block.style.left = 'calc(50% - ' + padding + 'px)';
        this._block.style.transform = 'translate(50%, -50%)';
        this.setTextBlock(this.imageBlock, fullImage);
        this.openBlock(this.imageBlock);
    }

    closeImg() {
        this.clearBlock(this.imageBlock);
        this.closeBlock(this.imageBlock);
    }

}
;

