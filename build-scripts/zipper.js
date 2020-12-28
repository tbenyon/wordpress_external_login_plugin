const zip = (readPath, writePath) => {
// require modules
  const fs = require('fs');
  const archiver = require('archiver');

    // create a file to stream archive data to.
    const output = fs.createWriteStream(__dirname + '/dist/archiver.zip');
    const archive = archiver('zip', {
      zlib: {level: 9} // Sets the compression level.
    });

// listen for all archive data to be written
// 'close' event is fired only when a file descriptor is involved
    output.on('close', function () {
      console.log('win!!!!!!!!!!', archive.pointer()) // Resolves with the total bytes
    });

// good practice to catch warnings (ie stat failures and other non-blocking errors)
    archive.on('warning', function (err) {
        throw error
    });

// good practice to catch this error explicitly
    archive.on('error', function (err) {
      throw err;
    });

// pipe archive data to the file
    archive.pipe(output);

// append files from a sub-directory, putting its contents at the root of archive
    archive.directory('../plugin-files/', false);

// finalize the archive (ie we are done appending files but streams have to finish yet)
// 'close', 'end' or 'finish' may be fired right after calling this method so register to them beforehand
    archive.finalize();
}

zip();
