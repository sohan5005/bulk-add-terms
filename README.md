# Bulk Add Terms
Wordpress plugin to add multiple taxonomy terms in one go.
Have you ever been frustrated adding more and more categories or tags or any other terms in a rush? Don't worry anymore. You can add thousands of terms in one go with this plugin.

## How do I do that?
1. Download the plugin from plugin repository: https://wordpress.org/plugins/bulk-add-terms/
2. Install the plugin and activate. A new menu called 'Add Bulk Terms' will pop up.
3. Click on the menu and you'll be taken to a new page where you will see all the registered taxonomy either by theme or any plugin.
4. First select a taxonomy which you want to add terms.
5. Then insert the terms in the right side textarea. Make sure each line contains only one term.
6. Click on 'Add Now' button. A little confirmation will pop up just to make sure you have inserted everything correctly. Click yes and BOOM. All terms are added.

## What about nesting parent and child?
This plugin supports to do that. You can go and do nesting. You can indent child levels with a dash (-). For example:

```
Foo
-Bar
-Baz
```

In the example above, 'foo' will be parent while 'bar' and 'baz' will be child of it.
You can use correct indent to make even more child of child. Example:

```
Foo
-Bar
--Baz
```

Complex nesting example:

```
Foo
-Child of Foo
--Grand child of Foo
-Second child of Foo
Baz is sibling of Foo
-Nephew of Foo
--Grand child of Baz
--Second grand child
-Son of Baz
-Daughter of Baz
I am a lonely term
Do not have child
```

Unfortunately the maximum supported level is as deep as the SEA.

## Features
* Unlimited terms per time
* Unlimited level of nesting
* Supports any registered taxonomy. (only those which can be added or removed within UI)
* Uses AJAX request

## Known issues
* Slugs are automatically generated.
* You can't add child items to those terms which are already added. If you try to do, the given parent item will add as a new term.

## Release notes
####1.0 Initial Release
####1.1 Features update
- New options page added
- Now same terms can be added to multiple taxonomies
- Non-hierarchical taxonomies can be hidden from options
- Added option to keep the text after adding terms
- Some small bug fixes

