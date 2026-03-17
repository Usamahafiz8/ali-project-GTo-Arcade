function getRandomInt(min, max) {
    var diff = max - min;
    return Math.floor(Math.random() * diff) + min;
}

function getRandomIntInclusive(min, max)
{
    return Math.floor(Math.random() * (max - min + 1) + min);
}

function toFloat(val) {
    var res = 0;
    try {
        res = parseFloat(val);
    } catch (e) {
        res = 0;
    }
    if (isNaN(res))
        res = 0;
    return res;
}

function in_array(needle, haystack, argStrict) { // eslint-disable-line camelcase
    let key = ''
    const strict = !!argStrict
    // we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] === ndl)
    // in just one for, in order to improve the performance
    // deciding wich type of comparation will do before walk array
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) { // eslint-disable-line eqeqeq
                return true
            }
        }
    }
    return false
}
module.exports = { getRandomInt, toFloat, in_array, getRandomIntInclusive };