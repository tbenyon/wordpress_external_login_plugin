/**
 * FREEMIUS DEPLOYMENT SCRIPT
 * Inspired by gulp-freemius-deploy (https://github.com/jamesckemp/gulp-freemius-deploy) by James Kemp (https://github.com/jamesckemp)
 */
// const zip = require("bestzip");
const needle = require("needle");
const fs = require("fs");
const cryptojs = require("crypto-js");
const decompress = require('decompress');
const zipper = require('./zipper')

const DIST_PATH = './dist';
const ZIPFILE = 'free.zip';
const ZIP_FILE_PATH = `${DIST_PATH}/${ZIPFILE}`;
const PLUGIN_FILES_SRC_PATH = '../plugin-files/'
const FREEMIUS_DEVELOPER_ID = process.env.FREEMIUS_DEVELOPER_ID;
const FREEMIUS_PLUGIN_ID = process.env.FREEMIUS_PLUGIN_ID;
const FREEMIUS_PK = process.env.FREEMIUS_PK;
const FREEMIUS_SK = process.env.FREEMIUS_SK;
const APIBASE = 'api.freemius.com'; // fast-api?????

console.log("-------------------");
console.log("FREEMIUS DEPLOYMENT");
console.log("-------------------");

console.log(`\nCreating deployment zip (${ZIPFILE})...`);


if (!fs.existsSync(DIST_PATH)){
  fs.mkdirSync(DIST_PATH);
}

// zip({
//   source: `${PLUGIN_FILES_SRC_PATH}/*`,
//   destination: ZIP_FILE_PATH,
// })



const runit = async () => {
  const promise = zipper.zip(PLUGIN_FILES_SRC_PATH, ZIP_FILE_PATH)

  console.log(promise);
  try {
    const resolved = await promise;
    console.log(resolved);
  } catch (e) {
    console.log(e);
  }
  console.log('fin');
}

runit();

return;

zipper.zip(PLUGIN_FILES_SRC_PATH, ZIP_FILE_PATH)
  .then(function () {
    console.log("Reading deployment zip...");
    const buffer = fs.readFileSync(ZIP_FILE_PATH);
    console.log('tombo', buffer);

    console.log("Authenticating...");

    const authDate = new Date().toUTCString();
    const authURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/token.json`;
    const authHeader = `FS ${FREEMIUS_DEVELOPER_ID}:${FREEMIUS_PK}:${cryptojs.enc.Base64.stringify(
      cryptojs.enc.Utf8.parse(
        cryptojs.HmacSHA256(["GET", "", "application/json", authDate, authURI].join("\n"), FREEMIUS_SK).toString()
      )
    ).replace(/=/g, "")}`;

    needle.get(
      `https://api.freemius.com${authURI}`,
      {
        headers: {
          "Content-MD5": "",
          "Content-Type": "application/json",
          Date: authDate,
          Authorization: authHeader,
        },
      },
      function (error, response, body) {
        if (error) {
          console.dir(error);
          console.log("\x1b[31m%s\x1b[0m", "Authentication error!");

          return;
        }

        if (typeof body.error !== "undefined") {
          console.log("\x1b[31m%s\x1b[0m", `Authentication failed (${body.error.message})!`);

          return;
        }

        const deployHeader = `FSA ${FREEMIUS_DEVELOPER_ID}:${body.access}`;
        const deployDate = new Date().toUTCString();
        const deployURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags.json`;
        const deployBoundary = "----" + new Date().getTime().toString(16);


        console.log("Uploading...");
        needle.post(
          `https://${APIBASE}${deployURI}`,
          {
            data: JSON.stringify({ add_contributor: false }),
            file: {
              buffer: buffer,
              filename: ZIPFILE,
              content_type: "application/zip",
            },
          },
          {
            open_timeout: 90000,
            stream_length: 0,
            multipart: true,
            boundary: deployBoundary,
            headers: {
              "Content-MD5": "",
              Date: deployDate,
              Authorization: deployHeader,
            },
          },
          function (error, response, body) {
            if (error) {
              console.dir(error);
              console.log("\x1b[31m%s\x1b[0m", "Upload error!");

              return;
            }

            if (typeof body === "object") {
              if (typeof body.error !== "undefined") {
                console.log("\x1b[31m%s\x1b[0m", `Upload failed (${body.error.message})!`);

                return;
              }

              if (body.id) {
                console.log(`Downloading v${body.version} from Freemius...`);

                const downloadURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags/${
                  body.id
                }.zip?authorization=${encodeURIComponent(deployHeader)}&is_premium=false`;

                needle.get(
                  `https://${APIBASE}${downloadURI}`,
                  {
                    follow_max: 10,
                    output: ZIPFILE,
                  },
                  function (error, response) {
                    if (error) {
                      console.dir(error);
                      console.log("\x1b[31m%s\x1b[0m", "Download error!");

                      return;
                    }

                    if (response.statusCode === 200) {
                      console.log("Preparing artifacts...");

                      decompress(ZIPFILE, "artifacts").then(function() {
                        console.log(`Plugin v${body.version} successfully deployed!`);
                      });

                    } else {
                      console.log("\x1b[31m%s\x1b[0m", "Download failed!");
                    }
                  }
                );
              } else {
                console.log("\x1b[31m%s\x1b[0m", "Invalid tag id!");
              }
            }
          }
        );
      }
    );
  })
  .catch(function (err) {
    console.error(err.stack);
    process.exit(1);
  });
