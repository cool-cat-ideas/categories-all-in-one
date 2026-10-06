const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const { test } = require('node:test');

const window = { location: { href: 'https://example.test/subdir/wp-admin/post.php' } };
vm.runInNewContext(fs.readFileSync(require.resolve('../js/rest-api.js'), 'utf8'), { window, URL });

for (const route of [
  'https://example.test/wp-json/categories-all-in-one/v1/categories',
  'https://example.test/subdir/wp-json/categories-all-in-one/v1/categories',
  'https://example.test/subdir/index.php?rest_route=/categories-all-in-one/v1/categories',
  'https://example.test/subdir/?rest_route=/categories-all-in-one/v1/categories&lang=pl'
]) {
  test(`Preserves the WordPress route and query: ${route}`, () => {
    const actual = new URL(window.CategoriesAllInOneRest.categoriesUrl({ categoriesUrl: route, postId: 123 }));
    const expected = new URL(route);
    assert.equal(actual.origin + actual.pathname, expected.origin + expected.pathname);
    for (const [key, value] of expected.searchParams) assert.equal(actual.searchParams.get(key), value);
    assert.equal(actual.searchParams.get('post_id'), '123');
  });
}
test('A widget without current content does not add a post ID', () => {
  const actual = window.CategoriesAllInOneRest.categoriesUrl({ categoriesUrl: 'https://example.test/?rest_route=/categories-all-in-one/v1/categories', postId: 0 });
  assert.equal(new URL(actual).searchParams.has('post_id'), false);
});
