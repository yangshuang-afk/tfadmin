var canClick = true;
var installIndex = 0;

String.prototype.format = function(args)
{
    if (arguments.length > 0)
    {
        var result = this;
        if (arguments.length === 1 && typeof (args) == "object")
        {
            for (var key in args)
            {
                var reg = new RegExp("({" + key + "})", "g");
                result = result.replace(reg, args[key]);
            }
        }
        else
        {
            for (var i = 0; i < arguments.length; i++)
            {
                if (arguments[i] === undefined)
                {
                    return "";
                }
                else
                {
                    var reg = new RegExp("({[" + i + "]})", "g");
                    result = result.replace(reg, arguments[i]);
                }
            }
        }
        return result;
    }
    else
    {
        return this;
    }
};

/**
 * 跳转到指定步骤
 */
function goStep(step) {
    if (canClick === false)
        return;

    canClick = false;
    document.main_form.action = "?step=" + step;
    document.main_form.submit();
}

/**
 * 返回上一步
 */
function cancel() {
    window.history.go(-1);
}