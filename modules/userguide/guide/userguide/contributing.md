# Contributing

Koseven is community driven, and we rely on community contributions for the documentation.

## Guidelines

Documentation should use complete sentences, good grammar, and be as clear as possible.  Use lots of example code, but make sure the examples follow the Koseven conventions and style.

Try to commit often, with each commit only changing a file or two, rather than changing a ton of files and commiting it all at once.  This will make it easier to offer feedback and merge your changes.   Make sure your commit messages are clear and descriptive.  Bad: "Added docs",  Good: "Added initial draft of hello world tutorial",  Bad: "Fixed typos",  Good: "Fixed typos on the query builder page"

If you feel a menu needs to be rearranged or a module needs new pages, please open a [bug report](http://koseven.dev/projects/userguide3/issues/new) to discuss it.

## Quick Method

To quickly point out something that needs improvement, report a [bug report](http://koseven.dev/projects/userguide3/issues/new).

If you want to contribute some changes, you can do so right from your browser without even knowing git!

First create an account on [GitHub](https://github.com/signup/free).

You will need to fork koseven <http://github.com/koseven/koseven>.

![Fork the module](contrib-github-fork.png)

The files that make the User Guide portion are found in `guide/<module>/`, and the API browser portion is made from the comments in the source code itself.  Navigate to one of the files you want to change and click the edit button in the top right of the file viewer.

![Click on edit to edit the file](contrib-github-edit.png)

Make the changes and add a **detailed commit message**.  Repeat this for as many files as you want to improve. (Note that you can't preview what the changes will look unless you actually test it locally.)

After you have made your changes, send a pull request so your improvements can be reviewed to be merged into the official documentation.

![Send a pull request](contrib-github-pull.png)

Once your pull request has been accepted, you can delete your repository if you want.  Your commit will have been copied to the official branch.

## If you know Git

### Short version

Fork koseven (e.g. `git://github.com/koseven/koseven.git`), checkout the `develop` branch make changes, and then send a pull request.

### Long version

(This still assumes you at least know your way around Git, especially how submodules work.)

 1. Fork the koseven repo you want on GitHub.
 
 1. Now you need to add your fork as a "git remote" to your application and ensure you are on the right branch. An example for the [ORM](../orm) module:

		cd my-koseven-app/modules/orm

		# add your repository as a new remote
		git remote add <your name> git://github.com/<your name>/koseven.git

		# Get the correct branch
		git checkout develop

 1. Now go into the repo of the area of docs you want to contribute to and add your forked repo as a new remote, and push to it.

		cd my-koseven-app/modules/orm/userguide

		# Make some changes to the docs
		nano file.md

		# Commit your changes - Use a descriptive commit message! If there is a redmine ticket for the changes you are making include "Fixes #XXXXX" in the commit message so its tracked.
		git commit -a -m "Corrected a typo in the ORM docs. Fixes #12345."

		# make sure we are up to date with the latest changes
		git merge origin/develop

		# Now push your changes to your fork.
		git push <your name> develop

 1. Finally, send a pull request on GitHub.
