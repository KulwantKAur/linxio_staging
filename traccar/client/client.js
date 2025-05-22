var net = require('net'),
  lineReader = require('line-reader'),
  serverHost = process.env.TRACCAR_HOST || 'traccar',
  serverGT06Port = process.env.TRACCAR_GT06_PORT || 5023,
  serverTeltonikaPort = process.env.TRACCAR_TELTONIKA_PORT || 5027,
  serverUlbotechPort = process.env.TRACCAR_TELTONIKA_PORT || 5072,
  serverMeitrackPort = process.env.TRACCAR_MEITRACK_PORT || 5020,
  serverQueclinkPort = process.env.TRACCAR_MEITRACK_PORT || 5004,
  serverDigitalMatterPort = process.env.TRACCAR_DIGITAL_MATTER_PORT || 5137,
  serverEELinkPort = process.env.TRACCAR_EELINK_PORT || 5064,
  // modify `serverPort` to one above for your needs
  serverPort = serverEELinkPort,
  messagesFileName = 'message.txt',
  lineNumber = 1,
  isLast = false
;

var client = new net.Socket();
client.connect(serverPort, serverHost, function() {
  console.log('Connected');
  readNewLine();
});

client.on('data', function(hex) {
  console.log(hex, ' hex');
  var data = hex.toString('hex');
  console.log('Received: ' + data);

  readNewLine();

  if (isLast) {
    client.destroy(); // kill client after server's response
  }
});

client.on('close', function() {
  console.log('Connection closed');
});

function readNewLine() {
  var currentLine = 1;

  lineReader.eachLine(messagesFileName, function(line, last) {
    if (currentLine === lineNumber) {
      client.write(new Buffer(line, 'hex'));
      console.log('Sent: ' + line);
      lineNumber++;

      if (last) {
        isLast = true;
      }

      return false;
    }

    currentLine++;
  });
}