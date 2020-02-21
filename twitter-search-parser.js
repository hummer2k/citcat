var twitterSearchParser = {
    init: function() {
        var jq = document.createElement('script');
        jq.src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js";
        document.getElementsByTagName('head')[0].appendChild(jq);
    },
    parse: function(offset, limit) {
        var items = $('#stream-items-id > li');
        var current = 0;

        if (typeof offset === 'string') {
            items = items.find('[data-item-id="' + offset + '"]');
        } else {
            items = items.slice(offset, (offset + limit));
        }

        items.each(function() {
            var tweet = $(this).find('> .tweet');
            window.setTimeout(function() {
                var hashtags = [];
                tweet.find('.js-tweet-text-container .twitter-hashtag').each(function() {
                    hashtags.push($(this).text());
                });
                var data = $.extend({}, {
                        retweetId: '',
                        retweeter: ''
                    },
                    tweet.data()
                );
                data.content = tweet.find('.js-tweet-text-container').text();
                data.time =  tweet.find('.js-short-timestamp').data('time');
                data.hashtags =  hashtags.join(',');


                jQuery.post('http://vdko.test/api/insert-tweet', data, function() {
                    current++;
                    console.log(current);
                });
            }, 150);
        });
    }
};
twitterSearchParser.init();

var twitterUserParser = {
    init: function() {
        var jq = document.createElement('script');
        jq.src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js";
        document.getElementsByTagName('head')[0].appendChild(jq);
    },
    parse: function () {

        var items = $('[data-component-context="user"] .js-actionable-user');
        items.each(function () {

            var user = $(this);

            window.setTimeout(function() {
                var userData = {
                    user_id: user.data('userId'),
                    screen_name: user.data('screenName'),
                    fullname: $.trim(user.find('.fullname').text()),
                    url: user.find('.fullname').attr('href'),
                    bio: $.trim(user.find('.ProfileCard-bio').text())
                };

                $.post('http://vdko.test/api/insert-user', userData, function () {
                    console.log('done.');
                })
            }, 150);
        });
    }
};
twitterUserParser.init();