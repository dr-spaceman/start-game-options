const API_ENDPOINT = '/api/search?q=';

const Search = () => {
  const [searchTerm, setSearchTerm] = React.useState('');
  const resultsInitialState = {
    hits: [],
    isLoading: false,
    isError: false
  };

  const resultsReducer = (state, action) => {
    switch (action.type) {
      case 'SEARCH_FETCH_INIT':
        return { ...state,
          isLoading: true,
          isError: false
        };

      case 'SEARCH_FETCH_SUCCESS':
        return { ...state,
          isLoading: false,
          isError: false,
          hits: action.payload
        };

      case 'SEARCH_FETCH_FAIL':
        return { ...state,
          isLoading: false,
          isError: true
        };

      case 'RESET':
        return { ...state,
          isLoading: false,
          isError: false,
          hits: []
        };

      default:
        throw new Error();
    }
  }; // call `dispatchResults` to change `results` object


  const [results, dispatchResults] = React.useReducer(resultsReducer, resultsInitialState);

  const handleSearch = event => {
    setSearchTerm(event.target.value);
  };

  React.useEffect(() => {
    if (!searchTerm) {
      dispatchResults({
        type: 'RESET'
      });
      return;
    }

    if (searchTerm.length < 3) {
      return;
    } // Mark search form as initializing/loading


    dispatchResults({
      type: 'SEARCH_FETCH_INIT'
    }); // Fetch from API

    const url = API_ENDPOINT + searchTerm;
    fetch(url).then(response => response.json()).then(result => {
      console.log('fetch result', result);

      if (!result.collection.items.length) {
        dispatchResults({
          type: 'SEARCH_FETCH_FAIL'
        });
      } else {
        dispatchResults({
          type: 'SEARCH_FETCH_SUCCESS',
          payload: result.collection.items
        });
      }
    }).catch(() => dispatchResults({
      type: 'SEARCH_FETCH_FAIL'
    }));
  }, [searchTerm]);
  return /*#__PURE__*/React.createElement("fieldset", {
    className: "inputwithlabel"
  }, /*#__PURE__*/React.createElement("label", {
    htmlFor: "searchform"
  }, "Search:"), ' ', /*#__PURE__*/React.createElement("input", {
    id: "searchform",
    type: "text",
    value: searchTerm,
    placeholder: "Search all the things",
    onChange: handleSearch
  }), ' ', /*#__PURE__*/React.createElement("button", {
    type: "reset",
    onClick: () => setSearchTerm('')
  }, "Reset"), results.isError && /*#__PURE__*/React.createElement("p", null, "Something went wrong"), results.isLoading ? /*#__PURE__*/React.createElement("p", null, "Loading...") : /*#__PURE__*/React.createElement(SearchResults, {
    results: results
  }));
};

function SearchResults(props) {
  const {
    results
  } = props;
  if (results.hits.length === 0) return null;
  return /*#__PURE__*/React.createElement("ul", null, results.hits.map(item => /*#__PURE__*/React.createElement(SearchResult, {
    key: item.title_sort,
    item: item
  })));
}
/**
 * Item component
 * @param {Object} props.item Item object
 * @param {} onRemoveItem
 */


function SearchResult(props) {
  const {
    item
  } = props;
  return /*#__PURE__*/React.createElement("li", null, /*#__PURE__*/React.createElement("a", {
    href: item.links.page
  }, /*#__PURE__*/React.createElement("dfn", null, item.title), ' ', /*#__PURE__*/React.createElement("span", null, "(", item.type, ")")));
}

ReactDOM.render(React.createElement(Search), document.getElementById('search'));