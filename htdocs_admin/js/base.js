// 全局命名空间
var K = this.K || {};

// 资源加载
K.Resource = K.Resource || {};

K.error = function(obj, type) {
	type = type || Error;
	throw new type(obj);
};

K.global = this;
K.__debug = true;

// 工具函数
(function(K) {

var ArrayProto = Array.prototype, ObjProto = Object.prototype, FuncProto = Function.prototype;

var slice			= ArrayProto.slice,
    unshift		  = ArrayProto.unshift,
    toString		 = ObjProto.toString,
    hasOwnProperty   = ObjProto.hasOwnProperty;

// ECMAScript 5 原生方法
var
	nativeForEach	  = ArrayProto.forEach,
	nativeMap		  = ArrayProto.map,
	nativeReduce	   = ArrayProto.reduce,
	nativeReduceRight  = ArrayProto.reduceRight,
	nativeFilter	   = ArrayProto.filter,
	nativeEvery		= ArrayProto.every,
	nativeSome		 = ArrayProto.some,
	nativeIndexOf	  = ArrayProto.indexOf,
	nativeLastIndexOf  = ArrayProto.lastIndexOf,
	nativeIsArray	  = Array.isArray,
	nativeKeys		 = Object.keys,
	nativeCreate     = Object.create,
	nativeBind		 = FuncProto.bind;

// 调试信息
K.log = function() {
	if (! K.__debug) {
		return;
	}
	if ('console' in window && 'log' in window.console) {
		var len = arguments.length;
		if (len == 1) {
			window.console.log(arguments[0]);
		} else if (len == 2) {
			window.console.log(arguments[0], arguments[1]);
		}
	} else if (window.opera && window.opera.postError) {
		window.opera.postError.apply(window.opera, arguments);
	}
};

// 集合操作函数
// -----------------

var breaker = {};

// 遍历集合
K.forEach = function(obj, iterator, context) {
	if (obj == null) {
		return;
	}
	if (nativeForEach && obj.forEach === nativeForEach) {
		obj.forEach(iterator, context);
	} else if (K.isNumber(obj.length)) {
		for (var i = 0, l = obj.length; i < l; i++) {
			if (iterator.call(context, obj[i], i, obj) == breaker) {
				return;
			}
	  	}
	} else {
		for (var key in obj) {
			if (hasOwnProperty.call(obj, key)) {
		  		if (iterator.call(context, obj[key], key, obj) === breaker) {
					return;
				}
			}
	  	}
	}
};

// 在对象中的每个属性项上运行一个函数，并将函数返回值作为属性的值。
K.map = function(obj, iterator, context) {
	var results = [];
	if (obj == null) {
		return results;
	}
	if (nativeMap && obj.map === nativeMap) {
		return obj.map(iterator, context);
	}
	K.forEach(obj, function(value, index, list) {
	  results[results.length] = iterator.call(context, value, index, list);
	});
	return results;
};

// 对集合元素进行递推操作
K.reduce = function(obj, iterator, memo, context) {
	var initial = memo !== void 0;
	if (obj == null) {
		obj = [];
	}
	if (nativeReduce && obj.reduce === nativeReduce) {
		if (context) {
			iterator = K.bind(iterator, context);
		}
		return initial ? obj.reduce(iterator, memo) : obj.reduce(iterator);
	}
	K.forEach(obj, function(value, index, list) {
		if (!initial && index === 0) {
			memo = value;
			initial = true;
	  	} else {
			memo = iterator.call(context, memo, value, index, list);
	  	}
	});
	if (!initial) {
		throw new TypeError("Reduce of empty array with no initial value");
	}
	return memo;
};

// 为集合元素进行逆向递推操作。
K.reduceRight = function(obj, iterator, memo, context) {
	if (obj == null) {
		obj = [];
	}
	if (nativeReduceRight && obj.reduceRight === nativeReduceRight) {
		if (context) iterator = K.bind(iterator, context);
	  	return memo !== void 0 ? obj.reduceRight(iterator, memo) : obj.reduceRight(iterator);
	}
	var reversed = (_.isArray(obj) ? obj.slice() : _.toArray(obj)).reverse();
	return _.reduce(reversed, iterator, memo, context);
};

// 在集合中查找符合条件的第一个元素
K.detect = function(obj, iterator, context) {
	var result;
	K.some(obj, function(value, index, list) {
		if (iterator.call(context, value, index, list)) {
			result = value;
			return true;
	  	}
	});
	return result;
};

// 在集合每一个元素上运行一个函数，将函数返回值为真的元素作为数组返回
K.filter = function(obj, iterator, context) {
	var results = [];
	if (obj == null) {
		return results;
	}
	if (nativeFilter && obj.filter === nativeFilter) {
		return obj.filter(iterator, context);
	}
	K.forEach(obj, function(value, index, list) {
		if (iterator.call(context, value, index, list)) results[results.length] = value;
	});
	return results;
};

// 判断是否集合中所有元素都为真
K.every = function(obj, iterator, context) {
	var result = true;
	if (obj == null) {
		return result;
	}
	if (nativeEvery && obj.every === nativeEvery) {
		return obj.every(iterator, context);
	}
	K.forEach(obj, function(value, index, list) {
	  if (!(result = result && iterator.call(context, value, index, list))) {
		  return breaker;
	  }
	});
	return result;
};

// 判断集合中是否有元素符合条件
K.some = function(obj, iterator, context) {
	iterator || (iterator = K.identity);
	var result = false;
	if (obj == null) {
		return result;
	}
	if (nativeSome && obj.some === nativeSome) {
		return obj.some(iterator, context);
	}
	K.forEach(obj, function(value, index, list) {
		if (iterator.call(context, value, index, list)) {
			result = true;
			return breaker;
		}
	});
	return result;
};

// 判断集合中是否包含某元素
K.contains = function(obj, target) {
	var found = false;
	if (obj == null) {
		return found;
	}
	if (nativeIndexOf && obj.indexOf === nativeIndexOf) {
		return obj.indexOf(target) != -1;
	}
	K.some(obj, function(value) {
	  if (found = value === target) {
		  return true;
	  }
	});
	return found;
};

// 返回包含集合中所有元素的key属性的数组
K.pluck = function(obj, key) {
	return K.map(obj, function(value) {
		return value[key];
	});
};

// 对集合元素进行排序
K.sortBy = function(obj, iterator, context) {
	return K.pluck(K.map(obj, function(value, index, list) {
		return {
			value : value,
			criteria : iterator.call(context, value, index, list)
	  	};
	}).sort(function(left, right) {
		var a = left.criteria, b = right.criteria;
		return a < b ? -1 : a > b ? 1 : 0;
	}), 'value');
};

// Use a comparator function to figure out at what index an object should
// be inserted so as to maintain order. Uses binary search.
K.sortedIndex = function(array, obj, iterator) {
	iterator || (iterator = K.identity);
	var low = 0, high = array.length;
	while (low < high) {
	  	var mid = (low + high) >> 1;
	  	iterator(array[mid]) < iterator(obj) ? low = mid + 1 : high = mid;
	}
	return low;
};

// 转化成数组
K.toArray = function(iterable) {
	if (!iterable)				return [];
	if (iterable.toArray)		 return iterable.toArray();
	if (K.isArray(iterable))	  return iterable;
	if (K.isArguments(iterable))  return slice.call(iterable);
	return K.values(iterable);
};

// 数组操作
// ---------------

// 获取数组最后一个元素
K.last = function(array) {
	var len = array.length;
	return len > 0 ? array[ len - 1 ] : undefined;
};

// 获取数组中真值的项。
K.compact = function(array) {
	return K.filter(array, function(value) {
		return !!value;
	});
};

// 将一个数组扁平化
K.flatten = function(array) {
	return K.reduce(array, function(memo, value) {
		if (K.isArray(value)) {
			return memo.concat(K.flatten(value));
	  	}
	  	memo[memo.length] = value;
	  	return memo;
	}, []);
};

// 将数组里的某(些)元素移除。
K.without = function(array, obj) {
	var values = slice.call(arguments, 1);
	return K.filter(array, function(value) {
		return !K.contains(values, value);
	});
};

// 数组元素除重，得到新数据
K.unique = function(array, isSorted) {
	return K.reduce(array, function(memo, el, i) {
		if (0 == i || (isSorted === true ? K.last(memo) != el : !K.contains(memo, el))) memo[memo.length] = el;
	  	return memo;
	}, []);
};

// 获取多个数组的交集
K.intersect = function(array) {
	var rest = slice.call(arguments, 1);
	return K.filter(K.unique(array), function(item) {
		return K.every(rest, function(other) {
			return K.indexOf(other, item) >= 0;
		});
	});
};

// 返回一个元素在数组中的位置（从前往后找）。如果数组里没有该元素，则返回-1
K.indexOf = function(array, item, isSorted) {
	if (array == null) return -1;
	var i, l;
	if (isSorted) {
		i = K.sortedIndex(array, item);
	  	return array[i] === item ? i : -1;
	}
	if (nativeIndexOf && array.indexOf === nativeIndexOf) {
		return array.indexOf(item);
	}
	for (i = 0, l = array.length; i < l; i++) if (array[i] === item) {
		return i;
	}
	return -1;
};

// 返回一个元素在数组中的位置（从后往前找）。如果数组里没有该元素，则返回-1
K.lastIndexOf = function(array, item) {
	if (array == null) return -1;
	if (nativeLastIndexOf && array.lastIndexOf === nativeLastIndexOf) {
		return array.lastIndexOf(item);
	}
	var i = array.length;
	while (i--) if (array[i] === item) {
		return i;
	}
	return -1;
};

// 函数操作
// ------------------

// 绑定
K.bind = function(func, context) {
	var extraArgs = Array.prototype.slice.call(arguments, 2);
	return function() {
		context = context || (this == K.global ? false : this);
		var args = extraArgs.concat(Array.prototype.slice.call(arguments));
		if (typeof(func) == "string" && context[func]) {
			context[func].apply(context, args);
		} else if (K.isFunction(func)) {
			return func.apply(context, args);
		} else {
			K.log("not function", func);
		}
	};
};


// 对函数进行methodize化，使其的第一个参数为this，或this[attr]。
K.methodize = function(func, attr) {
	if (attr) {
		return function() {
			return func.apply(null, [this[attr]].concat([].slice.call(arguments)));
		};
	}
	return function() {
		return func.apply(null, [this].concat([].slice.call(arguments)));
	};
};

K.extend = function(klass, proto) {
	var T = function() {}; //构造prototype-chain
	T.prototype = proto.prototype;

	var klassProto = klass.prototype;

	klass.prototype = new T();
	klass.$super = proto; //在构造器内可以通过arguments.callee.$super执行父类构造

	//如果原始类型的prototype上有方法，先copy
	K.mix(klass.prototype, klassProto, true);

	return klass;
};

// 以某对象为原型创建一个新的对象
K.create = nativeCreate || function(proto, props) {
	var ctor = function(ps) {
		if (ps) {
			K.mix(this, ps, true);
		}
	};
	ctor.prototype = proto;
	return new ctor(props);
};

// 延迟执行函数
K.delay = function(func, wait) {
	var args = slice.call(arguments, 2);
	return setTimeout(function() {
		return func.apply(func, args);
	}, wait);
};

K.defer = function(func) {
	return K.delay.apply(K, [func, 1].concat(slice.call(arguments, 1)));
};

// 对象操作
// ----------------

// 得到一个对象中所有可以被枚举出的属性的列表
K.keys = nativeKeys || function(obj) {
	if (obj !== Object(obj)) {
		throw new TypeError('Invalid object');
	}
	var keys = [];
	for (var key in obj) if (hasOwnProperty.call(obj, key)) {
		keys[keys.length] = key;
	}
	return keys;
};

// 得到一个对象中所有可以被枚举出的属性值的列表
K.values = function(obj) {
	return K.map(obj, K.identity);
};

// 得到一个对象中所有可以被枚举出的方法列表
K.methods = function(obj) {
	return K.filter(K.keys(obj), function(key) {
		return _.isFunction(obj[key]);
	}).sort();
};

// 将源对象的属性并入到目标对象
K.mix = function(obj) {
	K.forEach(slice.call(arguments, 1), function(source) {
		for (var prop in source) if (source[prop] !== void 0) {
			obj[prop] = source[prop];
	  	}
	});
	return obj;
};

// 克隆
K.clone = function(obj) {
	return K.isArray(obj) ? obj.slice() : K.mix({}, obj);
};

// 是否为空数组或对象
K.isEmpty = function(obj) {
	if (K.isArray(obj) || K.isString(obj)) {
		return obj.length === 0;
	}
	for (var key in obj) if (hasOwnProperty.call(obj, key)) {
		return false;
	}
	return true;
};

// 判断一个变量是否是Html的Element元素
K.isElement = function(obj) {
	return !!(obj && obj.nodeType == 1);
};

// 判断对象是否为数组
K.isArray = nativeIsArray || function(obj) {
	return toString.call(obj) === '[object Array]';
};

// 判断对象是否为函数
K.isFunction = function(obj) {
	return !!(obj && obj.constructor && obj.call && obj.apply);
};

if (toString.call(arguments) == '[object Arguments]') {
    K.isArguments = function(obj) {
        return toString.call(obj) == '[object Arguments]';
    };
} else {
    K.isArguments = function(obj) {
        return !!(obj && hasOwnProperty.call(obj, 'callee'));
    };
}


// 判断对象是否为字符串
K.isString = function(obj) {
	return !!(obj === '' || (obj && obj.charCodeAt && obj.substr));
};

// 判断对象是否为数字
K.isNumber = function(obj) {
	return !!(obj === 0 || (obj && obj.toExponential && obj.toFixed));
};

// 判断对象是否是Nan
K.isNaN = function(obj) {
	return obj !== obj;
};

// 判断对象是否为boolean类型
K.isBoolean = function(obj) {
	return obj === true || obj === false;
};

// 判断对象是否为Date类型
K.isDate = function(obj) {
	return !!(obj && obj.getTimezoneOffset && obj.setUTCFullYear);
};

// 判断对象是否为正则
K.isRegExp = function(obj) {
	return !!(obj && obj.test && obj.exec && (obj.ignoreCase || obj.ignoreCase === false));
};

// 判断对象是否为null
K.isNull = function(obj) {
	return obj === null;
};

// 判断对象是否为undefined
K.isUndefined = function(obj) {
	return obj === void 0;
};

// 字符函数
// -----------------

// 除去字符串两边的空白字符
K.trim = function(str) {
	return str.replace(/^[\s\xa0\u3000]+|[\u3000\xa0\s]+$/g, "");
};

// 得到字节长度
K.byteLen = function(str) {
	return str.replace(/[^\x00-\xff]/g, "--").length;
};

// 得到指定字节长度的子字符串
K.subByte = function(str, len, tail) {
	if (K.byteLen(str) < len) {
		return str;
	}
	tail = tail || '';
	len -= K.byteLen(tail);
	return str.substr(0, len).replace(/([^\x00-\xff])/g, "$1 ") //双字节字符替换成两个
			  .substr(0, len) // 截取长度
			  .replace(/[^\x00-\xff]$/, "") //去掉临界双字节字符
			  .replace(/([^\x00-\xff]) /g, "$1") + tail; //还原
};

// 字符串为javascript转码
K.encode4JS = function(str) {
	return str.replace(/\\/g, "\\u005C")
			  .replace(/"/g, "\\u0022")
			  .replace(/'/g, "\\u0027")
			  .replace(/\//g, "\\u002F")
			  .replace(/\r/g, "\\u000A")
			  .replace(/\n/g, "\\u000D")
			  .replace(/\t/g, "\\u0009");
};

// 为http的不可见字符、不安全字符、保留字符作转码
K.encode4HTTP = function(str) {
	return str.replace(/[\u0000-\u0020\u0080-\u00ff\s"'#\/\|\\%<>\[\]\{\}\^~;\?\:@=&]/, function(s) {
		return encodeURIComponent(s);
	});
};

/**
* 字符串为Html转码
* @method encode4Html
* @static
* @param {String} s 字符串
* @return {String} 返回处理后的字符串
* @example
	var s="<div>dd";
	alert(encode4Html(s));
*/
K.encode4Html = function(s){
	return s.replace(/&(?!\w+;|#\d+;|#x[\da-f]+;)/gi, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#x27;').replace(/\//g,'&#x2F;');
};

/**
* 字符串为Html的value值转码
* @method encode4HtmlValue
* @static
* @param {String} s 字符串
* @return {String} 返回处理后的字符串
* @example:
	var s="<div>\"\'ddd";
	alert("<input value='"+encode4HtmlValue(s)+"'>");
*/
K.encode4HtmlValue = function(s){
	return K.encode4Html(s).replace(/"/g,"&quot;").replace(/'/g,"&#039;");
}

// 将所有tag标签消除，即去除<tag>，以及</tag>
K.stripTags = function(str) {
	return str.replace(/<[^>]*>/gi, '');
};

//日期函数
//------------------
/**
* 格式化日期
* @method format
* @static
* @param {Date} d 日期对象
* @param {string} pattern 日期格式(y年M月d天h时m分s秒)，默认为"yyyy-MM-dd"
* @return {string}  返回format后的字符串
* @example
	var d=new Date();
	alert(format(d," yyyy年M月d日\n yyyy-MM-dd\n MM-dd-yy\n yyyy-MM-dd hh:mm:ss"));
*/
K.formatDate = function(d,pattern){
	pattern=pattern||"yyyy-MM-dd";
	var y=d.getFullYear();
	var o = {
		"M" : d.getMonth()+1, //month
		"d" : d.getDate(),    //day
		"h" : d.getHours(),   //hour
		"m" : d.getMinutes(), //minute
		"s" : d.getSeconds() //second
	}
	pattern=pattern.replace(/(y+)/ig,function(a,b){var len=Math.min(4,b.length);return (y+"").substr(4-len);});
	for(var i in o){
		pattern=pattern.replace(new RegExp("("+i+"+)","g"),function(a,b){return (o[i]<10 && b.length>1 )? "0"+o[i] : o[i]});
	}
	return pattern;
}


K.post = function(uri, para, succ_callback, fail_callback) {
	$.post(uri, para, function(data){
		if ( data && data.hasOwnProperty('error')){
			if ('function' == typeof fail_callback) {
                fail_callback( data.error );
            } else {
                alert( data.error.errmsg );
            }
		} else {
			if ('function' == typeof succ_callback) succ_callback( data.payload );
		}
	}, 'json');
}

/**
 * 页面跳转
 * @param string href
 * @param json params
 */
K.location = function (href, params){
	if (typeof params !== 'object'){
		return alert('参数格式错误，参数必须是json格式！');
	}
	var paramStr = '';
	for (i in params){
		paramStr = paramStr + i + '=' + encodeURIComponent(params[i]) + '&';
	}
	paramStr = paramStr.slice(0, paramStr.length - 1);
	href = href + '?' + paramStr;
	window.location.href = href;
}

/**
 * 全选/反选，调用方式 K.checkbox('#idName', '.calssName');
 * @param string selectAllButton 操作全选/复选按钮id或class，如'#idName'/'.className'
 * @param string checkboxContainer 待选框容器 使用id或class，如'#idName'/'.className'
 */
K.checkbox = function (selectAllButton, checkboxContainer){
	$(selectAllButton).on('click', function(){
        if ($(this).is(':checked')) {
            $(checkboxContainer).find('input[type="checkbox"]').each(function(){
                this.checked = true;
            });
        } else {
            $(checkboxContainer).find('input[type="checkbox"]').each(function(){
            	this.checked = false;
            });
        }
    });
	var allSellected = false;
	$(checkboxContainer).on('click', function (){
		$(checkboxContainer).find('input[type="checkbox"]').each(function(){
            if (this.checked == false) {
            	allSellected = false;
            }
        });
		if (allSellected == false) {
			$(selectAllButton).each(function(){
			    this.checked = false;
			});
		} else {
    		$(selectAllButton).each(function(){
			    this.checked = true;
			});
		}
		allSellected = true;
	});
}

/**
 * 获取复选框选中的value值 调用方式 K.getSelectedVal('.className', 'array');
 * @param string checkboxContainer 使用id或class，如'#idName'/'.className'
 * @param string resultDataType string / array
 * @return string | array
 */
K.getSelectedVal = function (checkboxContainer, resultDataType){
	var result = null;
	if (resultDataType == 'array'){
		var selected_arr = new Array();
		$.each($(checkboxContainer + ' input:checkbox:checked'),function(){
			selected_arr.push(Number($(this).val()));
	    });
		result = selected_arr;
	} else if(resultDataType == 'string'){
		var selected_str = new String();
		$.each($(checkboxContainer + ' input:checkbox:checked'),function(){
			selected_str = selected_str + $(this).val() + ',';
	    });
		result = selected_str.slice(0, selected_str.length - 1);
	}
	return result;
}

$('input[type=text]').focus(function(ev){
	var tgt = $(ev.currentTarget);
	if (tgt.attr('placeholder') && tgt.attr('placeholder') == tgt.val()) {
		tgt.val('');
	}
});


$('a._j_show_login_dlg').click(function(ev){
	ev.preventDefault();
	$('#_j_login_div').show();
});

$('.sex_boy').attr('title', '男');
$('.sex_gril').attr('title', '女');

// 轮询获取系统消息
//setInterval(function(){
//    K.post('/user/ajax/get_message_count.php', '', function(data){
//        var count = data.count;
//        if(count>0){
//            $('#tip_message_top').html(count).show();
//            $('#tip_message_body').html(count).show();
//        }else{
//            $('#tip_message_top').html(count).hide();
//            $('#tip_message_body').html(count).hide();
//        }
//    });
//}, 30000);

}(K));
