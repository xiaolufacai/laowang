/**
 * 格式化时间戳
 * @param timestamp
 * @param format
 * @returns {*}
 */
export function formatTimestamp(timestamp, format) {
    if (timestamp) {
        timestamp = timestamp * 1000;
    } else {
        timestamp = new Date().getTime()
    }
    let date = new Date(timestamp);
    if (!format) {
        format = 'YYYY-MM-DD HH:mm:ss';
    }
    let year = date.getFullYear();
    let month = ("0" + (date.getMonth() + 1)).slice(-2);
    let day = ("0" + date.getDate()).slice(-2);
    let hours = ("0" + date.getHours()).slice(-2);
    let minutes = ("0" + date.getMinutes()).slice(-2);
    let seconds = ("0" + date.getSeconds()).slice(-2);

    format = format.replace('YYYY', year);
    format = format.replace('MM', month);
    format = format.replace('DD', day);
    format = format.replace('HH', hours);
    format = format.replace('mm', minutes);
    format = format.replace('ss', seconds);

    return format;
}
