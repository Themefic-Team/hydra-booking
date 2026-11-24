/**
 * Safe REST URL helpers for the admin app.
 *
 * Never concatenate a route param or other variable straight onto
 * tfhb_core_apps.rest_route. With plain permalinks rest_route is
 * "index.php?rest_route=/", so a raw "?", "&" or "#" inside a value adds
 * query parameters (a second rest_route, _method, ...) to a request that is
 * sent with the user's X-WP-Nonce. That is how a crafted
 * #/bookings/update/<payload> link turned into POST /wp/v2/users.
 */

/**
 * True when the value is a positive integer id (1, 2, 42 ...).
 *
 * @param {*} value
 * @returns {boolean}
 */
export const tfhbIsPositiveInt = (value) => /^[1-9]\d*$/.test(String(value ?? '').trim());

/**
 * Build a REST URL from a fixed path and dynamic segments. Each segment is
 * passed through encodeURIComponent and segments are joined with "/".
 *
 * tfhbRestUrl('hydra-booking/v1/booking/', id)
 *   -> <rest_route>hydra-booking/v1/booking/<encoded id>
 *
 * @param {string} path   Fixed, trusted path ending with "/".
 * @param {...*}   segments Untrusted values (route params, ids from API data).
 * @returns {string}
 */
export const tfhbRestUrl = (path, ...segments) => {
    const encoded = segments.map((segment) => encodeURIComponent(String(segment ?? '').trim()));
    return tfhb_core_apps.rest_route + path + encoded.join('/');
};
