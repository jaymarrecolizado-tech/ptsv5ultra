/**
 * Debounce Utility
 * Delays function execution until after a specified wait period
 */

function debounce(func, wait = 300) {
    let timeout;
    let result;
    let latestArgs;
    let latestThis;

    return function(...args) {
        latestThis = this;
        latestArgs = args;

        if (timeout) {
            clearTimeout(timeout);
        }

        return new Promise((resolve) => {
            timeout = setTimeout(() => {
                result = func.apply(latestThis, latestArgs);
                resolve(result);
            }, wait);
        });
    };
}

function throttle(func, limit = 300) {
    let inThrottle;
    let lastResult;

    return function(...args) {
        if (!inThrottle) {
            lastResult = func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
        return lastResult;
    };
}
