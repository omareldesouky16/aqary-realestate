try {
  const plugin = require('@angular-devkit/build-angular/plugins/karma');
  console.log('Success:', plugin);
} catch (e) {
  console.error('Error:', e.message);
}
