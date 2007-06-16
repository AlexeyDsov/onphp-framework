<?php
	// $Id$
	// doxygen helper file

	/**
	 * @mainpage onPHP
	 * 
	 * For support consider using our <a href="http://onphp.org/contacts.en.html">maling lists</a>.
	 *
	 * <hr>
	 *
	 * - quasi-persistent layer:
	 *  - OSQL query builder:
	 *   - SelectQuery;
	 *   - InsertQuery;
	 *   - UpdateQuery;
	 *   - DeleteQuery;
	 *  - DB abstraction layer:
	 *   - connectors:
	 *    - PgSQL;
	 *    - MySQL;
	 *    - SQLite;
	 *    - IBase (incubator);
	 *    - MSSQL (incubator);
	 *    - OraSQL (incubator);
	 *   - utils:
	 *    - Queue;
	 *    - TransactionQueue;
	 *    - DBTransaction;
	 *  - DAO hierarchies:
	 *   - GenericDAO workers:
	 *    - NullDaoWorker;
	 *    - CommonDaoWorker;
	 *    - TransparentDaoWorker:
	 *     - SmartDaoWorker;
	 *     - VoodooDaoWorker;
	 * - IdentifiableObject collections:
	 *  - StorableContainer;
	 *  - UnifiedContainer;
	 * - Cache subsystem:
	 *  - peers:
	 *   - Memcached (and PeclMemcached);
	 *   - RubberFileSystem;
	 *   - SharedMemory;
	 *   - RuntimeMemory;
	 *  - locking thru SemaphorePool:
	 *   - SystemFiveLocker;
	 *   - FileLocker;
	 *   - DirectoryLocker;
	 *  - utils:
	 *   - AggregateCache;
	 *   - ReferencePool;
	 *
	 * ...
	 *
	 * @defgroup Core Core classes
	 *
	 * Core classes and interfaces you just can't live without
	 *
	 * @defgroup Base Widely used base classes and interfaces
	 * @ingroup Core
	 *
	 * @defgroup Cache Application-wide cache subsystem
	 * @ingroup Core
	 *
	 * @defgroup Lockers Different locking methods implementation
	 * @ingroup Cache
	 *
	 * @defgroup DB Connectors and dialects for various databases
	 * @ingroup Core
	 *
	 * @defgroup Exceptions Exceptions
	 * @ingroup Core
	 *
	 * @defgroup Form Data validation layer
	 * @ingroup Core
	 *
	 * @defgroup Filters Tools for primitive's filtration
	 * @ingroup Form
	 *
	 * @defgroup Primitives Base data types used in Form
	 * @ingroup Form
	 *
	 * @defgroup Logic Logical expressions used in OSQL and Form
	 * @ingroup Core
	 *
	 * @defgroup OSQL Dynamic query builder
	 * @ingroup Core
	 *
	 * @defgroup Main Higher level classes
	 *
	 * @defgroup Helpers Common wrapper and helper classes
	 * @ingroup Main
	 *
	 * @defgroup Containers IdentifiableObject collections handlers
	 * @ingroup Main
	 *
	 * @defgroup DAOs Root classes for building DAO hierarchies
	 * @ingroup Main
	 *
	 * @defgroup Module Business logic containers
	 * @ingroup Main
	 *
	 * @defgroup Utils Various accompanying utilities
	 * @ingroup Main
	 *
	 * @defgroup Calendar Calendar representation's helpers
	 * @ingroup Main
	 *
	 * @defgroup Flow Spring-like webflow tools.
	 * @ingroup Main
	 *
	 * Useful stuff for building complex and scalable applications.
	**/
?>
