const API_ENDPOINT = '/api.php?query=';

const Search = () => {
    console.log('Search component');

    const [searchTerm, setSearchTerm] = React.useState('');
    console.log('State:searchTerm', searchTerm)

    const handleSearch = event => {
        console.log('onInputChange event triggered', event)
        setSearchTerm(event.target.value)
    }
    
    const resultsInitialState = {
        hits: [], 
        isLoading: false, 
        isError: false 
    };
    const resultsReducer = (state, action) => {
        console.log('Results Reducer', state, action)

        switch (action.type) {
            case 'GAMES_FETCH_INIT':
                return {
                    ...state,
                    isLoading: true,
                    isError: false,
                }
            case 'GAMES_FETCH_SUCCESS':
                return {
                    ...state,
                    isLoading: false,
                    isError: false,
                    hits: action.payload,
                }
            case 'GAMES_FETCH_FAIL':
                return {
                    ...state,
                    isLoading: false,
                    isError: true,
                }
            default:
                throw new Error()
        }
    };
    // call `dispatchResults` to change `results` object
    const [results, dispatchResults] = React.useReducer(resultsReducer, resultsInitialState);
    console.log('State:results', results);

    // Actions to take on (re-)render if searchTerm changes
    React.useEffect(() => {
        console.log('Side effect init: Dependency:searchTerm')

        // Mark search form as initializing/loading
        dispatchResults({ type: 'GAMES_FETCH_INIT' });

        // Fetch from API
        fetch(API_ENDPOINT + searchTerm)
            .then(response => response.json())
            .then(result => {
                dispatchResults({
                    type: 'GAMES_FETCH_SUCCESS',
                    payload: result,
                })
            })
            .catch(() => dispatchResults({ type: 'GAMES_FETCH_FAIL' }))
    }, [searchTerm])

    const searchResults = results.hits

    return (
        <>
            <InputWithLabel id="searchform" value={searchTerm} placeholder="Search all the things" onInputChange={handleSearch} isFocused>
                Search:
            </InputWithLabel>

            <SearchResults results={searchResults} />
        </>
    );
}

/**
 * InputWithLabel component
 * @param {String} id 
 * @param {String} type Input type form field
 * @param {String} value The search term
 * @param {Event} onInputChange
 * @param {Boolean} isFocused Reference to the input field's focus
 * @param {String} children Inner HTML of the component
 * @param {String} placeholder
 */
function InputWithLabel(props) {
    console.log('InputWithLabel component', props)

    const { id, type = 'text', children, value, onInputChange, numResults, isFocused, placeholder } = props

    // Create a reference to an element
    // This will later be assigned to the text input element so we can reference it elsewhere in the component
    // Reference is persistent value for lifetime of component
    const inputRef = React.useRef()

    React.useEffect(() => {
        console.log('useEffect:isFocused', isFocused, inputRef)
        // Access the ref.current property, a mounted text input element
        if (isFocused && inputRef.current) {
            inputRef.current.focus()
        }
    }, [isFocused])

    return (
        <>
            <fieldset>
                <label htmlFor={id}>{children}</label>
                <input ref={inputRef} id={id} type={type} value={value} onChange={onInputChange} placeholder={placeholder} />
            </fieldset>
        </>
    )

}

function SearchResults(props) {
    console.log('SearchResults component', props)

    const {results} = props;

    if (!results) return '';

    return (
        <ul>
            {results.map(item => <RearchResult key={item=title_sort} item={item} />)}
        </ul>
    )
}

/**
 * Item component
 * @param {Object} props.item Item object
 * @param {} onRemoveItem
 */
function RearchResult(props) {
    console.log('RearchResult component', props)

    const { item } = props

    return (
        <li>
            <dt>Title</dt>
            <dd>{item.title}</dd>
            <dt>Genre</dt>
            <dd>{item.genre}</dd>
            <dt>Platform</dt>
            <dd>{item.platform}</dd>
            <dt>Release</dt>
            <dd>{item.release}</dd>
        </li>
    )
}

ReactDOM.render(
    React.createElement(Search),
    document.getElementById('search')
);