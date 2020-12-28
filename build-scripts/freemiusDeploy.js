/**
 * FREEMIUS DEPLOYMENT SCRIPT
 * Inspired by gulp-freemius-deploy (https://github.com/jamesckemp/gulp-freemius-deploy) by James Kemp (https://github.com/jamesckemp)
 */
const fs = require("fs");
const cryptojs = require("crypto-js");
const decompress = require('decompress');
const zipper = require('./zipper')
const axios = require('axios');
const FormData = require('form-data');

const DIST_PATH = './dist';
const ZIPFILE = 'free.zip';
const ZIP_FILE_PATH = `${DIST_PATH}/${ZIPFILE}`;
const PLUGIN_FILES_SRC_PATH = '../plugin-files/'
const FREEMIUS_DEVELOPER_ID = process.env.FREEMIUS_DEVELOPER_ID;
const FREEMIUS_PLUGIN_ID = process.env.FREEMIUS_PLUGIN_ID;
const FREEMIUS_PK = process.env.FREEMIUS_PK;
const FREEMIUS_SK = process.env.FREEMIUS_SK;
const APIBASE = 'api.freemius.com';

console.log("-------------------");
console.log("FREEMIUS DEPLOYMENT");
console.log("-------------------");

console.log(`\nCreating deployment zip (${ZIPFILE})...`);

if (!fs.existsSync(DIST_PATH)){
  fs.mkdirSync(DIST_PATH);
}

(async function () {
  try {
    await zipper.zip(PLUGIN_FILES_SRC_PATH, ZIP_FILE_PATH);
  } catch(e) {
    console.log('Unable to Zip plugin\n', e);
    process.exit(1);
  }

  try {
    const token = await deployZipToFreemius();
    console.log('TOKEN TIME!!!', token);
  } catch (e) {
    console.log('Unable to get auth token\n', e);
    process.exit(2);
  }
})();

function deployZipToFreemius() {
  return getFreemiusAuthTokens()
    .then((tokens) => {
      const deployHeader = `FSA ${FREEMIUS_DEVELOPER_ID}:${tokens.access}`;
      const deployDate = new Date().toUTCString();
      const deployURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags.json`;
      const deployBoundary = "----" + new Date().getTime().toString(16);

      const form = new FormData();
      form.append('file', fs.createReadStream(ZIP_FILE_PATH));

      const request_config = {
        headers: {
          "Content-MD5": "",
          Date: deployDate,
          Authorization: deployHeader,
          ...form.getHeaders()
        }
      };
      return axios.post(
        `https://${APIBASE}${deployURI}`,
        form,
        request_config
      );
    })
    .catch((err) => {
      console.log('failed upload', err);
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

  //
  //             if (body.id) {
  //               console.log(`Downloading v${body.version} from Freemius...`);
  //
  //               const downloadURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags/${
  //                 body.id
  //               }.zip?authorization=${encodeURIComponent(deployHeader)}&is_premium=false`;
  //
  //               needle.get(
  //                 `https://${APIBASE}${downloadURI}`,
  //                 {
  //                   follow_max: 10,
  //                   output: ZIPFILE,
  //                 },
  //                 function (error, response) {
  //                   if (error) {
  //                     console.dir(error);
  //                     console.log("\x1b[31m%s\x1b[0m", "Download error!");
  //
  //                     return;
  //                   }
  //
  //                   if (response.statusCode === 200) {
  //                     console.log("Preparing artifacts...");
  //
  //                     decompress(ZIPFILE, "artifacts").then(function() {
  //                       console.log(`Plugin v${body.version} successfully deployed!`);
  //                     });
  //
  //                   } else {
  //                     console.log("\x1b[31m%s\x1b[0m", "Download failed!");
  //                   }
  //                 }
  //               );
  //             } else {
  //               console.log("\x1b[31m%s\x1b[0m", "Invalid tag id!");
  //             }
  //           }
  //         }
  //       );
  //     }
  //   );
  // })
  // .catch(function (err) {
  //   console.error(err.stack);
  //   process.exit(1);
  // });
  //
