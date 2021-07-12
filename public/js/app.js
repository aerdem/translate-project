$(document).ready(function () {
    jQuery('.ui.search.dropdown')
        .dropdown({
            fullTextSearch: true
        });

    $('#translateButton').click(function () {
        let sourceText = $('#sourceText').val();
        if (!sourceText) {
            alert("Lütfen kaynak texti giriniz");
            return;
        }
        let targetLanguage = $('#targetLanguage').dropdown('get value');
        if (!targetLanguage) {
            alert("Lütfen hedef dil seçiniz");
            return;
        }

        let sourceLanguage = $('#sourceLanguage').dropdown('get value');

        $.ajax({
            type: 'POST',
            url: '/getTranslate',
            data: {
                sourceLanguage: sourceLanguage,
                targetLanguage: targetLanguage,
                sourceText: sourceText,
            },
            async: true,
            jsonpCallback: 'callback',
            //contentType: "application/json",
            dataType: 'jsonp',
            success: function (data) {
                console.log(data);
                fillTranslate(data[0]);
            },
            error: function () {
                console.log('failed');
            }
        });
    });

    function fillTranslate(data) {
        if (data.detectedLanguage) {
            let selectLanguageItem = $(".sourceLanguageItems").find("[data-value='" + data.detectedLanguage.language + "']")
            let eachILanguageItems = $(".sourceLanguageItems > .item");
            $.each(eachILanguageItems, function (index, item) {
                $(item).removeClass("active selected");
            });
            $(selectLanguageItem).addClass("active selected");
            $('.sourceActiveText').html($(selectLanguageItem).html());
        }
        $('#targetText').val(data.translations[0].text);
    }

    $('#historyButton').click(function () {
        $.ajax({
            type: 'GET',
            url: '/getHistory',
            data: {},
            async: true,
            jsonpCallback: 'callback',
            dataType: 'jsonp',
            success: function (data) {
                console.log(data);
                fillHistory(data);
            },
            error: function () {
                console.log('failed');
            }
        });
    });

    function fillHistory(data) {
        let html = '';
        $.each(data, function (index, item) {
            console.log(item);
            html += '<div class="ui card">\n' +
                '            <div class="content">\n' +
                '                <div classs="ui sub header">' + item.languageCodes.sourceLanguage + ' <i class="icon arrow right"></i> ' + item.languageCodes.targetLanguage + '</div>\n' +
                '                   <div class="summary">\n' +
                item.requestParams.sourceText +
                '                    </div>\n' +
                '           <div class="content">\n' +
                '                   <div class="meta">\n' +
                item.responseParams[0].translations[0].text +
                '                   </div>\n' +
                '           </div>' +
                '           <div classs="ui divider"></div>' +
                '        </div>';
        });

        console.log(html);
        $('.sidebarContent').html(html);
        $('.sidebarTitle').html("History");
        $('.ui.sidebar')
            .sidebar('toggle')
        ;
    }
});