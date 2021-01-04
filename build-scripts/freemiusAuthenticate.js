/**
 * FREEMIUS DEPLOYMENT SCRIPT
 * Inspired by gulp-freemius-deploy (https://github.com/jamesckemp/gulp-freemius-deploy) by James Kemp (https://github.com/jamesckemp)
 */
const cryptojs = require("crypto-js");
const axios = require('axios');

const FREEMIUS_DEVELOPER_ID = process.env.FREEMIUS_DEVELOPER_ID;
const FREEMIUS_PLUGIN_ID = process.env.FREEMIUS_PLUGIN_ID;
const FREEMIUS_PK = process.env.FREEMIUS_PK;
const FREEMIUS_SK = process.env.FREEMIUS_SK;

console.log("-------------------");
console.log("FREEMIUS Authenticate");
console.log("-------------------");

function getAuthHeaderValue() {
  return getFreemiusAuthTokens().then((tokens) => {
    if (!tokens.access) throw new Error('No access token returned');
    return `FSA ${FREEMIUS_DEVELOPER_ID}:${tokens.access}`
  })
}

function getFreemiusAuthTokens() {
  console.log("Authenticating...");

  const authDate = new Date().toUTCString();
  const authURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/token.json`;
  const authHeader = `FS ${FREEMIUS_DEVELOPER_ID}:${FREEMIUS_PK}:${cryptojs.enc.Base64.stringify(
    cryptojs.enc.Utf8.parse(
      cryptojs.HmacSHA256(["GET", "", "application/json", authDate, authURI].join("\n"), FREEMIUS_SK).toString()
    )
  ).replace(/=/g, "")}`;

  return axios(`https://api.freemius.com${authURI}`, {
    method: 'get',
    headers: {
      "Content-MD5": "",
      "Content-Type": "application/json",
      Date: authDate,
      Authorization: authHeader,
    },
  })
  .then(json => {
    if (typeof json.error !== "undefined") {
      throwFetchAuthTokenError(json.error);
    }
    return json.data;
  })
  .catch(err => throwFetchAuthTokenError(err));

  function throwFetchAuthTokenError(reason) {
    throw new Error(`Failed to fetch Feemius auth token:\n${reason}`);
  }
}

exports.getAuthHeaderValue = getAuthHeaderValue;
