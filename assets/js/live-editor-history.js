/**
 * Live Page Builder — undo/redo history (shell side).
 *
 * A simple bounded snapshot stack of the document JSON. Phase 0 exposes the
 * API and keeps the Undo/Redo buttons in sync; the editing that pushes real
 * snapshots arrives in later phases. Global on window for the other modules.
 */
( function () {
	'use strict';

	var LIMIT = 60;

	function History() {
		this.stack = [];
		this.index = -1;
		this.onChange = function () {};
	}

	History.prototype.reset = function ( snapshot ) {
		this.stack = [ clone( snapshot ) ];
		this.index = 0;
		this.onChange();
	};

	History.prototype.push = function ( snapshot ) {
		// Drop any redo tail, then append.
		this.stack = this.stack.slice( 0, this.index + 1 );
		this.stack.push( clone( snapshot ) );
		if ( this.stack.length > LIMIT ) { this.stack.shift(); }
		this.index = this.stack.length - 1;
		this.onChange();
	};

	History.prototype.canUndo = function () { return this.index > 0; };
	History.prototype.canRedo = function () { return this.index < this.stack.length - 1; };

	History.prototype.undo = function () {
		if ( ! this.canUndo() ) { return null; }
		this.index--;
		this.onChange();
		return clone( this.stack[ this.index ] );
	};

	History.prototype.redo = function () {
		if ( ! this.canRedo() ) { return null; }
		this.index++;
		this.onChange();
		return clone( this.stack[ this.index ] );
	};

	History.prototype.current = function () {
		return this.index >= 0 ? clone( this.stack[ this.index ] ) : null;
	};

	function clone( o ) { return JSON.parse( JSON.stringify( o ) ); }

	window.OSSLPBHistory = History;
} )();
