(function(K) {

	var cookies = document.cookie,
		islogin = false;

	if(K) {
		K.Env = K.Env || {};

		K.mix(K.Env, {

			'COOKIE_UID' : '_admin_uid',

			'UID' : (function(){
				var reg = /(^| )_admin_uid=([^;]*)(;|$)/,
					UIDMatch = cookies.match(reg);
				islogin = !!UIDMatch;
				return UIDMatch && unescape(UIDMatch[2]);
			}()),

			'isLogin' : function() {
				var reg = /(^| )_admin_session=([^;]*)(;|$)/,
					match = document.cookie.match(reg);
				return !!match;
			}

		});
	}

}(K));