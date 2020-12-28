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
const archiver = require('archiver');

// (async () => {
//   console.log('before');
//   const txt = await fs.promises.readFile('yarn-error.log', 'utf-8');
//   console.log('after', txt);
// })();

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
//
// const zip = (readPath, writePath) => {
//   console.log('zipppppp')
//   const outputStream = fs.createWriteStream(__dirname + writePath);
//   const archive = archiver('zip', {
//     zlib: {level: 9} // Sets the compression level.
//   });
//
//   console.log('zipppppp2')
//   archive.pipe(outputStream);
//
//   archive.directory(readPath, false);
//
//   const promise = archive.finalize();
//   console.log('zipppppp3', promise)
//   return promise;
// }
const zip = (readPath, writePath) => {
  return new Promise((resolve, reject) => {
    console.log('zipppppp')
    const outputStream = fs.createWriteStream(__dirname + writePath);
    const archive = archiver('zip', {
      zlib: {level: 9} // Sets the compression level.
    });

    // listen for all archive data to be written
// 'close' event is fired only when a file descriptor is involved
    outputStream.on('close', function() {
      console.log(archive.pointer() + ' total bytes');
      console.log('archiver has been finalized and the output file descriptor has closed.');
      resolve(archive.pointer());
    });

// This event is fired when the data source is drained no matter what was the data source.
// It is not part of this library but rather from the NodeJS Stream API.
// @see: https://nodejs.org/api/stream.html#stream_event_end
    outputStream.on('end', function() {
      console.log('Data has been drained');
    });

// good practice to catch warnings (ie stat failures and other non-blocking errors)
    archive.on('warning', function(err) {
      reject(err);
    });

    archive.on('error', function(err) {
      reject(err);
    });

    console.log('zipppppp2')
    archive.pipe(outputStream);

    archive.directory(readPath, false);

    const promise = archive.finalize();
    console.log('zipppppp3', promise)
    return promise;
  })
}

if (!fs.existsSync(DIST_PATH)){
  fs.mkdirSync(DIST_PATH);
}

// zip({
//   source: `${PLUGIN_FILES_SRC_PATH}/*`,
//   destination: ZIP_FILE_PATH,
// })

console.log('hmmmmm111');

(async function () {
  try {

    const promise = zip(PLUGIN_FILES_SRC_PATH, ZIP_FILE_PATH);
    console.log(promise);
    // setInterval(() => console.log(promise), 100)
    console.log('before await');
    const result = await promise;
    console.log('after await', result);
  } catch(e) {
    console.log('uhh!!!!!!!');
    process.exit(1);
  }
  console.log('end of try catch')
})();

console.log('hmmmmm222');

(async () => {
  await new Promise((resolve) => {
    setTimeout(() => {
      console.log('timeout finished');
      resolve();
    }, 5000)
  })
})();

console.log('hmmmmm333');

// zipper.zip(PLUGIN_FILES_SRC_PATH, ZIP_FILE_PATH)
//   .then(function () {
//     console.log("Reading deployment zip...");
//     const buffer = fs.readFileSync(ZIP_FILE_PATH);
//     console.log('tombo', buffer);
//
//     console.log("Authenticating...");
//
//     const authDate = new Date().toUTCString();
//     const authURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/token.json`;
//     const authHeader = `FS ${FREEMIUS_DEVELOPER_ID}:${FREEMIUS_PK}:${cryptojs.enc.Base64.stringify(
//       cryptojs.enc.Utf8.parse(
//         cryptojs.HmacSHA256(["GET", "", "application/json", authDate, authURI].join("\n"), FREEMIUS_SK).toString()
//       )
//     ).replace(/=/g, "")}`;
//
//     needle.get(
//       `https://api.freemius.com${authURI}`,
//       {
//         headers: {
//           "Content-MD5": "",
//           "Content-Type": "application/json",
//           Date: authDate,
//           Authorization: authHeader,
//         },
//       },
//       function (error, response, body) {
//         if (error) {
//           console.dir(error);
//           console.log("\x1b[31m%s\x1b[0m", "Authentication error!");
//
//           return;
//         }
//
//         if (typeof body.error !== "undefined") {
//           console.log("\x1b[31m%s\x1b[0m", `Authentication failed (${body.error.message})!`);
//
//           return;
//         }
//
//         const deployHeader = `FSA ${FREEMIUS_DEVELOPER_ID}:${body.access}`;
//         const deployDate = new Date().toUTCString();
//         const deployURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags.json`;
//         const deployBoundary = "----" + new Date().getTime().toString(16);
//
//
//         console.log("Uploading...");
//         needle.post(
//           `https://${APIBASE}${deployURI}`,
//           {
//             data: JSON.stringify({ add_contributor: false }),
//             file: {
//               buffer: buffer,
//               filename: ZIPFILE,
//               content_type: "application/zip",
//             },
//           },
//           {
//             open_timeout: 90000,
//             stream_length: 0,
//             multipart: true,
//             boundary: deployBoundary,
//             headers: {
//               "Content-MD5": "",
//               Date: deployDate,
//               Authorization: deployHeader,
//             },
//           },
//           function (error, response, body) {
//             if (error) {
//               console.dir(error);
//               console.log("\x1b[31m%s\x1b[0m", "Upload error!");
//
//               return;
//             }
//
//             if (typeof body === "object") {
//               if (typeof body.error !== "undefined") {
//                 console.log("\x1b[31m%s\x1b[0m", `Upload failed (${body.error.message})!`);
//
//                 return;
//               }
//
//               if (body.id) {
//                 console.log(`Downloading v${body.version} from Freemius...`);
//
//                 const downloadURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags/${
//                   body.id
//                 }.zip?authorization=${encodeURIComponent(deployHeader)}&is_premium=false`;
//
//                 needle.get(
//                   `https://${APIBASE}${downloadURI}`,
//                   {
//                     follow_max: 10,
//                     output: ZIPFILE,
//                   },
//                   function (error, response) {
//                     if (error) {
//                       console.dir(error);
//                       console.log("\x1b[31m%s\x1b[0m", "Download error!");
//
//                       return;
//                     }
//
//                     if (response.statusCode === 200) {
//                       console.log("Preparing artifacts...");
//
//                       decompress(ZIPFILE, "artifacts").then(function() {
//                         console.log(`Plugin v${body.version} successfully deployed!`);
//                       });
//
//                     } else {
//                       console.log("\x1b[31m%s\x1b[0m", "Download failed!");
//                     }
//                   }
//                 );
//               } else {
//                 console.log("\x1b[31m%s\x1b[0m", "Invalid tag id!");
//               }
//             }
//           }
//         );
//       }
//     );
//   })
//   .catch(function (err) {
//     console.error(err.stack);
//     process.exit(1);
//   });
//
