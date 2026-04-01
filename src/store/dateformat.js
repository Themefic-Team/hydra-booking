export default function useDateFormat() {

    const getDateFormatSetting = () => {
        if (typeof tfhb_core_apps === 'undefined' || !tfhb_core_apps) {
            return '';
        }

        return (tfhb_core_apps.date_format || '').toString().trim();
    };

    const pad = (value) => String(value).padStart(2, '0');

    const monthNamesLong = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const monthNamesShort = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];

    const dayNamesLong = [
        'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
    ];

    const dayNamesShort = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    const formatDateByPattern = (dateValue, pattern) => {
        const date = new Date(dateValue);
        if (Number.isNaN(date.getTime())) {
            return dateValue || '';
        }

        if (!pattern) {
            return date.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        const tokenMap = {
            d: pad(date.getDate()),
            j: String(date.getDate()),
            m: pad(date.getMonth() + 1),
            n: String(date.getMonth() + 1),
            Y: String(date.getFullYear()),
            y: String(date.getFullYear()).slice(-2),
            F: monthNamesLong[date.getMonth()],
            M: monthNamesShort[date.getMonth()],
            l: dayNamesLong[date.getDay()],
            D: dayNamesShort[date.getDay()],
            U: String(Math.floor(date.getTime() / 1000)),
            c: date.toISOString(),
            r: date.toUTCString()
        };

        let output = '';
        for (let i = 0; i < pattern.length; i++) {
            const char = pattern[i];

            if (char === '\\') {
                i += 1;
                output += pattern[i] || '';
                continue;
            }

            output += Object.prototype.hasOwnProperty.call(tokenMap, char) ? tokenMap[char] : char;
        }

        return output;
    };

    const Tfhb_Date = (dateString) => {
        if (!dateString) return '';
        const dateFormat = getDateFormatSetting();
        const dates = dateString.split(',');  
        // If no global date format is set, keep legacy display behavior.
        const dateObjects = dates.map(date => {
            const trimmedDate = (date || '').trim();
            if (!trimmedDate) {
                return '';
            }

            if (!dateFormat) {
                const options = { day: 'numeric', month: 'long', year: 'numeric' };
                const dateObject = new Date(trimmedDate);
                return Number.isNaN(dateObject.getTime()) ? trimmedDate : dateObject.toLocaleDateString('en-US', options);
            }

            return formatDateByPattern(trimmedDate, dateFormat);
        }); 
        return dateObjects.filter(Boolean).join(', '); 
    }

    const Tfhb_Time = (timeString) => {
        const options = { hour: '2-digit', minute: '2-digit', hour12: true };
        const time = new Date(timeString);
        return time.toLocaleTimeString('en-US', options);
    }

    // full date and time format
    const Tfhb_DateTime = (dateString) => {
        if (!dateString) return '';

        const date = new Date(dateString);
        if (Number.isNaN(date.getTime())) {
            return dateString;
        }

        const dateFormat = getDateFormatSetting();
        const formattedDate = dateFormat
            ? formatDateByPattern(date, dateFormat)
            : date.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });

        const formattedTime = date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        return `${formattedDate}, ${formattedTime}`;

    }
    
    return { Tfhb_Date, Tfhb_Time, Tfhb_DateTime }
}