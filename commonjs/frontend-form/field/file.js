// @require ModernizrNetworkXhr2
var $ = require('jQuery');
var fieldRegistry = require('kwf/frontend-form/field-registry');
var Field = require('kwf/frontend-form/field/field');
var kwfExtend = require('kwf/extend');
var t = require('kwf/trl');

var File = kwfExtend(Field, {
    initField: function() {
        if (!Modernizr.xhr2) {
            return;
        }

        this.el.addClass('dropField');
        this.dropContainer = this.el;
        this.fileInput = this.el.find('input[type="file"]');
        this.uploadIdField = this.dropContainer.find('input.kwfUploadIdField');
        this.fileSizeLimit = this.fileInput.data('fileSizeLimit');

        // Prevent Event-Bubbling
        this.dropContainer.on('dragenter', function(e) {
            e.stopPropagation();
            e.preventDefault();
        });

        this.dropContainer.on('dragover', function(e) {
            e.stopPropagation();
            e.preventDefault();
        });

        this.dropContainer.get(0).addEventListener('drop', this.onDrop.bind(this));
        this.fileInput.get(0).addEventListener('change', this.onDrop.bind(this));

    },
    onDrop: function(e) {
        e.stopPropagation();
        e.preventDefault();

        var files = e.dataTransfer ? e.dataTransfer.files : e.target.files;
        if (!files.length) return;

        this.form.disableSubmit();

        var file = files[0];

        if (file.size > this.fileSizeLimit) {
            return alert(t.trlKwf('Allowed upload size exceeded max. allowed upload size {0} MB', this.fileSizeLimit/1048576));
        }

        var progressbar = $(
            '<div class="kwfFormFieldUploadProgressBar">' +
                '<div class="inner">' +
                    '<span class="progress"></span>' +
                    '<span class="processing">'+t.trlKwf("Processing")+'...</span>' +
                '</div>' +
            '</div>');

        this.dropContainer.prepend(progressbar);

        var uploadIdField = this.uploadIdField;

        var xhr = new XMLHttpRequest();
        var url = '/kwf/media/upload/json-upload';
        if (Kwf.sessionToken) url += '?kwfSessionToken='+Kwf.sessionToken;
        xhr.open('POST', url);
        xhr.setRequestHeader('X-Upload-Name', encodeURIComponent(file.name));
        xhr.setRequestHeader('X-Upload-Size', file.size);
        xhr.setRequestHeader('X-Upload-Type', file.type);
        var ua = navigator.userAgent.toLowerCase();
        if (!ua.match(/trident/) && !ua.match(/edge/)) {
            xhr.overrideMimeType('text/plain; charset=x-user-defined-binary');
        }


        xhr.upload.onprogress = (function(data) {
            if (data.lengthComputable) {
                var progress = (data.loaded / data.total) * 100;
                if (progress < 100) {
                    progressbar.find('span.progress').css('width', progress+'%');
                } else {
                    progressbar.find('span.progress').css('width', '100%');
                    progressbar.find('span.progress').hide();
                    progressbar.find('span.processing').addClass('visible');
                }
            }
        }).bind(this);

        xhr.send(file);

        xhr.onreadystatechange = (function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                this.form.enableSubmit();

                progressbar.fadeOut(function() {
                    $(this).remove();
                });

                var response;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {
                    return alert(t.trlKwf('An error occured, please try again later'));
                }
                this.dropContainer.find('input.fileSelector').val('');
                uploadIdField.val(response.value.uploadId+'_'+response.value.hashKey);
                this.dropContainer.find('input.kwfFormFieldFileUnderlayText').val(response.value.filename);

            } else if (xhr.readyState == 4 && xhr.status !== 200) {
                this.form.enableSubmit();
                return alert(t.trlKwf('An error occured, please try again later'));
            }
        }).bind(this);
    },
    getFieldName: function() {
        var inp = this.el.find('input.fileSelector');
        if (!inp) return null;
        return inp.get(0).name;
    },
    getValue: function() {
        var inp = this.el.find('input[type="hidden"]');
        if (!inp) return null;
        var ret = inp.get(0).value;
        return ret;
    },
    clearValue: function() {
        var inp = this.el.find('input[type="hidden"]');
        inp.get(0).value = '';
    },
    setValue: function(value) {
        var inp = this.el.find('input[type="hidden"]');
        inp.get(0).value = value;
    }
});

fieldRegistry.register('kwfFormFieldFile', File);
module.exports = File;