
const API_ENDPOINT = '/api/search?q=';

const Search = () => {
    console.log('Search component');

    const [searchTerm, setSearchTerm] = React.useState('');
    console.log('State:searchTerm', searchTerm)
    
    const resultsInitialState = {
        hits: [], 
        isLoading: false, 
        isError: false 
    };
    const resultsReducer = (state, action) => {
        console.log('Results Reducer', state, action)

        switch (action.type) {
            case 'SEARCH_FETCH_INIT':
                return {
                    ...state,
                    isLoading: true,
                    isError: false,
                }
            case 'SEARCH_FETCH_SUCCESS':
                return {
                    ...state,
                    isLoading: false,
                    isError: false,
                    hits: action.payload,
                }
            case 'SEARCH_FETCH_FAIL':
                return {
                    ...state,
                    isLoading: false,
                    isError: true,
                }
            case 'RESET':
                return {
                    ...state,
                    isLoading: false,
                    isError: false,
                    hits: []
                }
            default:
                throw new Error()
        }
    };
    // call `dispatchResults` to change `results` object
    const [results, dispatchResults] = React.useReducer(resultsReducer, resultsInitialState);
    console.log('State:results', results);
    
    const handleSearch = event => {
        console.log('onInputChange event triggered', event)
        
        setSearchTerm(event.target.value);
    }

    React.useEffect(() => {
        console.log('Effect:searchTerm');

        if (!searchTerm) {
            dispatchResults({ type: 'RESET' });
            return;
        }

        // Mark search form as initializing/loading
        dispatchResults({ type: 'SEARCH_FETCH_INIT' });
        
        // Fetch from API
        let url = API_ENDPOINT + searchTerm;
        console.log('fetch', url);

        fetch(url)
        .then(response => response.json())
        .then(result => {
            console.log('fetch result', result);

            if (! result.collection.items.length) {
                dispatchResults({ type: 'SEARCH_FETCH_FAIL' })
            } else {
                dispatchResults({
                    type: 'SEARCH_FETCH_SUCCESS',
                    payload: result.collection.items,
                })
            }
        })
        .catch(() => dispatchResults({ type: 'SEARCH_FETCH_FAIL' }))
    }, [searchTerm]);
    
    return (
        <fieldset className="inputwithlabel">
            <label htmlFor="searchform">Search:</label>
            <input id="searchform" type="text" value={searchTerm} placeholder="Search all the things" onChange={handleSearch} /> 
            <button type="reset" onClick={() => setSearchTerm('')}>Reset</button>

            {results.isError && <p>Something went wrong</p>}

            {results.isLoading ? (<p>Loading...</p>) : (<SearchResults results={results} />)}
        </fieldset>
    );
}

function SearchResults(props) {
    console.log('SearchResults component', props)

    let {results} = props;console.log('results', results);

    if (results.hits.length === 0) return null;

    return (
        <ul>
            {results.hits.map(item => <SearchResult key={item.title_sort} item={item} />)}
        </ul>
    )
}

/**
 * Item component
 * @param {Object} props.item Item object
 * @param {} onRemoveItem
 */
function SearchResult(props) {
    // console.log('SearchResult component', props)

    const { item } = props

    return (
        <li>
            <a href={item.url}>
                <dfn>{item.title}</dfn> 
                <span>({item.type})</span>
            </a>
        </li>
    )
}

ReactDOM.render(
    React.createElement(Search),
    document.getElementById('search')
);