var Queue = new Class({

    Implements: [Options, Events],

    options: {
		maximum: undefined,
		/*
		onProcessed: function()
		{
			//console.log('queue processed, total: ', total);
		},
		*/		
    },

    initialize: function(manager, options) {
		this.manager = manager; // uploadhtml5 object
        if (!options) options = {};
        this.setOptions(options);
		this._data = [];
		this.options.totalsize = 0;
		this.options.totalcount = 0;
    },

	add: function(el)
	{
		// TODO If < maximum
		this._data.push(el);
		file = el;
		//console.log('queue size LEFT', this.sizeLeft());
		//console.log('added to queue', file.name, file.size, file.type);
		this.options.totalsize = this.options.totalsize + file.size;
		this.countIncrease(1);
		//this.sizeIncrease(file.size);
		//console.log('total bytes in queue', this.sizeLeft());
		
	},

	get: function()
	{
		return this._data.pop();
	},

	sizeTotal: function()
	{
		return this.options.totalsizemax;
	},
	
	sizeLeft: function()
	{
		return this.options.totalsize;
	},
	
	sizeIncrease: function(bytes)
	{
		this.options.totalsize = this.options.totalsize + bytes;
		return this.options.totalsize;
	},
	
	sizeDecrease: function(bytes)
	{
		this.options.totalsize = this.options.totalsize - bytes;
		if (this.options.totalsize < 0) this.options.totalsize = 0;
		return this.options.totalsize;
	},
	
	countIncrease: function(n)
	{
		this.options.totalcount = this.options.totalcount + n;
		return this.options.totalcount;
	},
	
	countDecrease: function(n)
	{
		this.options.totalcount = this.options.totalcount - n;
		return this.options.totalcount;
	},
	
	countLeft: function()
	{
		return this.options.totalcount;
	},
	
	
	process: function()
	{
		this.options.totalsizemax = this.sizeLeft();
		//console.log('TOTAL SIZE AT START', this.sizeTotal());
		var total = this._data.length;
		for (var i=0; i < total; i++)
		{
			//console.log('queue(this).process() next, total', i, total);
			this.manager.process(this.get());
		}
		//this.fireEvent('queue async processed (jobs started)');
	},

	state: function()
	{
		return true;
	},
	

})

