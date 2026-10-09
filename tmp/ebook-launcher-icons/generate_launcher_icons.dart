import 'dart:io';

import 'package:flutter_launcher_icons/main.dart' as launcher_icons;
import 'package:image/image.dart' as img;

/// Packages the existing brand artwork without stretching or redrawing it.
/// Run from the app directory: dart run tool/generate_launcher_icons.dart
Future<void> main() async {
  final source = img.decodePng(File('assets/logo.png').readAsBytesSync());
  if (source == null) {
    throw StateError('Cannot decode assets/logo.png');
  }

  const size = 1024;
  final logo = img.copyResize(
    source,
    width: 900,
    interpolation: img.Interpolation.cubic,
  );
  final foreground = img.Image(width: size, height: size, numChannels: 4);
  img.compositeImage(
    foreground,
    logo,
    dstX: (size - logo.width) ~/ 2,
    dstY: (size - logo.height) ~/ 2,
  );
  final icon = img.Image(width: size, height: size, numChannels: 3);
  img.fill(icon, color: img.ColorRgb8(255, 255, 255));
  img.compositeImage(icon, foreground);

  Directory('assets/branding').createSync(recursive: true);
  File('assets/branding/app_icon.png').writeAsBytesSync(img.encodePng(icon));
  File('assets/branding/app_icon_foreground.png')
      .writeAsBytesSync(img.encodePng(foreground));

  await launcher_icons.createIconsFromArguments([
    '-f',
    'flutter_launcher_icons.yaml',
  ]);
}
