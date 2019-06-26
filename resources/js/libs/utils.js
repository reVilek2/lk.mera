const PRECISION = 10000;

function amountToExternal(val) {
    if (typeof val !== 'number') {
        val = Number(val);
    }
    return val * PRECISION;
}

function amountToReadable(val) {
    if (typeof val !== 'number') {
        val = Number(val);
    }
    return val / PRECISION;
}

function amountToHumanize(val)
{
    if (typeof val === 'number') {
        val = val.toString();
    }
    let tmp = val.split('.');
    let out = number_format(tmp[0], 0, '.', ' ');

    if (tmp[1]) {
        out += '.' + tmp[1];
    }

    return out + ' руб.';
}

function amountToRaw(val) {
    if (val) {
        val = val.replace(/\sруб\./g, '');
        if (typeof val === 'number') {
            val = val.toString();
        }
        let tmp = val.split('.');
        let out = tmp[0].replace(/\D/g, '');
        if (tmp[1]) {
            out += '.'+tmp[1].replace(/\D/g, '');
        }
        if (typeof out !== 'number') {
            out = Number(out);
        }
        return out*10/10;
    }
    return 0;
}

/***
 number - число
 decimals - количество знаков после разделителя
 dec_point - символ разделителя
 separator - разделитель тысячных
 ***/
function number_format(number, decimals, dec_point, separator ) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    let n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof separator === 'undefined') ? ',' : separator ,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            let k = Math.pow(10, prec);
            return '' + (Math.round(n * k) / k)
                .toFixed(prec);
        };
    // Фиксим баг в IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
        .split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
        .length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1)
            .join('0');
    }
    return s.join(dec);
}

export {
    amountToExternal,
    amountToReadable,
    amountToHumanize,
    amountToRaw,
    number_format,
}