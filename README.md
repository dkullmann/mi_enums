# Mi Enums. Enum fields you can edit

Mi Enums provides the benfits of enum fields without the drawbacks. If you're unaware, enum fields are not portable - they don't work on all databases and build into applications restrictions which often become problematic. Mi Enums stores all enums in a (cached) database table such that any DB or data store can benefit from enumerated fields as well as being no problem should an existing enum value become obsolete or a new one need to be added.
