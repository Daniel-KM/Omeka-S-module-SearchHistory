Search History (module for Omeka S)
===================================

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[Search History] is a module for [Omeka S] that allows user to save search and
to callback it via the guest account.

Note that a similar module exists: [Selection], that allows not only to select
resources and to store them in selection sets, but to create dynamic selection
with a query.


Installation
------------

See general end user documentation for [installing a module].

This module requires the modules [Guest] and [Common], that should be installed first.

* From the zip

Download the last release [SearchHistory.zip] from the list of releases, and
uncompress it in the `modules` directory.

* From the source and for development

If the module was installed from the source, rename the name of the folder of
the module to `SearchHistory`.

Then install it like any other Omeka module and follow the config instructions.


Usage
-----

A button is added in the bottom of the browse view (via theme or via module [BlocksDisposition]),
so the user can save the last search. The user can see the search history via a
link in the guest user account.


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitLab.


License
-------

This module is published under the [CeCILL v2.1] license, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

This software is governed by the CeCILL license under French law and abiding by
the rules of distribution of free software. You can use, modify and/ or
redistribute the software under the terms of the CeCILL license as circulated by
CEA, CNRS and INRIA at the following URL "http://www.cecill.info".

As a counterpart to the access to the source code and rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software’s author, the holder of the economic rights, and the
successive licensors have only limited liability.

In this respect, the user’s attention is drawn to the risks associated with
loading, using, modifying and/or developing or reproducing the software by the
user in light of its specific status of free software, that may mean that it is
complicated to manipulate, and that also therefore means that it is reserved for
developers and experienced professionals having in-depth computer knowledge.
Users are therefore encouraged to load and test the software’s suitability as
regards their requirements in conditions enabling the security of their systems
and/or data to be ensured and, more generally, to use and operate it in the same
conditions as regards security.

The fact that you are presently reading this means that you have had knowledge
of the CeCILL license and that you accept its terms.


Copyright
---------

* Copyright Daniel Berthereau, 2019-2025 (see [Daniel-KM] on GitLab)

This module was first developed for the [Fondation Maison de Salins].


[Search History]: https://gitlab.com/Daniel-KM/Omeka-S-module-SearchHistory
[Omeka S]: https://omeka.org/s
[Guest]: https://gitlab.com/Daniel-KM/Omeka-S-module-Guest
[Common]: https://gitlab.com/Daniel-KM/Omeka-S-module-Common
[Selection]: https://gitlab.com/Daniel-KM/Omeka-S-module-Selection
[installing a module]: https://omeka.org/s/docs/user-manual/modules/#installing-modules
[module issues]: https://gitlab.com/Daniel-KM/Omeka-S-module-SearchHistory/-/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Fondation Maison de Salins]: https://collections.maison-salins.fr
[GitLab]: https://gitlab.com/Daniel-KM
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
