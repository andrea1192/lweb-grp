<?php

	switch ($class) {
		// Models
		case 'models\AbstractMapper':
		case 'models\AbstractMovieMapper':
		case 'models\MovieMapper':
		case 'models\RequestMapper':
		case 'models\PostMapper':
		case 'models\CommentMapper':
		case 'models\ReviewMapper':
		case 'models\QuestionMapper':
		case 'models\SpoilerMapper':
		case 'models\ExtraMapper':
		case 'models\ReactionMapper':
		case 'models\BinaryRatingMapper':
		case 'models\NumericRatingMapper':
		case 'models\LikeMapper':
		case 'models\UsefulnessMapper':
		case 'models\AgreementMapper':
		case 'models\SpoilageMapper':
		case 'models\AnswerMapper':
		case 'models\ReportMapper':
			require_once 'models/AbstractMapper.php';
			break;
		case 'models\AbstractModel':
		case 'models\InvalidDataException':
			require_once 'models/AbstractModel.php';
			break;
		case 'models\AbstractMovie':
		case 'models\Movie':
		case 'models\Request':
			require_once 'models/Movie.php';
			break;
		case 'models\AbstractMovies':
		case 'models\Movies':
		case 'models\Requests':
		case 'models\MovieList':
		case 'models\RequestList':
			require_once 'models/Movies.php';
			break;
		case 'models\Post':
		case 'models\RatedPost':
		case 'models\Comment':
		case 'models\Review':
		case 'models\Question':
		case 'models\Spoiler':
		case 'models\Extra':
			require_once 'models/Post.php';
			break;
		case 'models\Posts':
		case 'models\Comments':
		case 'models\PostList':
			require_once 'models/Posts.php';
			break;
		case 'models\Reaction':
		case 'models\BinaryRating':
		case 'models\NumericRating':
		case 'models\ReactionType':
		case 'models\Like':
		case 'models\Usefulness':
		case 'models\Agreement':
		case 'models\Spoilage':
		case 'models\Answer':
		case 'models\Report':
		case 'models\BinaryReactionType':
		case 'models\NumericReactionType':
			require_once 'models/Reaction.php';
			break;
		case 'models\Reactions':
		case 'models\Answers':
		case 'models\Reports':
		case 'models\ReactionList':
			require_once 'models/Reactions.php';
			break;
		case 'models\IRepository':
			require_once 'models/Repository.php';
		case 'models\User':
			require_once 'models/User.php';
			break;
		case 'models\Database':
		case 'models\Users':
			require_once 'models/Users.php';
			break;
		case 'models\XMLDocument':
		case 'models\ElementList':
			require_once 'models/XMLDocument.php';

		// Views
		case 'views\AbstractView':
			require_once 'views/AbstractView.php';
		case 'views\AbstractCollectionView':
		case 'views\AbstractListView':
		case 'views\AbstractGridView':
		case 'views\MoviesView':
		case 'views\ReportsView':
		case 'views\UsersView':
			require_once 'views/CollectionViews.php';
			break;
		case 'views\DialogView':
		case 'views\SetupView':
		case 'views\SigninView':
		case 'views\SignupView':
		case 'views\ProfileView':
		case 'views\PasswordChangeView':
		case 'views\AccountDeleteView':
		case 'views\UserEditView':
			require_once 'views/DialogViews.php';
			break;
		case 'views\Movie':
		case 'views\Request':
			require_once 'views/Movie.php';
			break;
		case 'views\AbstractEditView':
		case 'views\MovieView':
		case 'views\MovieEditView':
		case 'views\MovieComposeView':
			require_once 'views/MovieView.php';
			break;
		case 'views\Post':
		case 'views\RatedPost':
		case 'views\Comment':
		case 'views\Review':
		case 'views\Question':
		case 'views\Spoiler':
		case 'views\Extra':
			require_once 'views/Post.php';
			break;
		case 'views\AbstractPostView':
		case 'views\PostView':
		case 'views\PostEditView':
		case 'views\PostComposeView':
		case 'views\ReactionCreateView':
			require_once 'views/PostView.php';
			break;
		case 'views\Reaction':
		case 'views\Answer':
		case 'views\Report':
		case 'views\ReactionType':
			require_once 'views/Reaction.php';
			break;
		case 'views\UIComponents':
			require_once 'views/UIComponents.php';
			break;
		case 'views\User':
			require_once 'views/User.php';
			break;

		// Controllers
		case 'controllers\SetupController':
			require_once 'install.php';
			break;
		case 'controllers\LoginController':
			require_once 'login.php';
			break;
		case 'controllers\MovieController':
			require_once 'movie.php';
			break;
		case 'controllers\MoviesController':
			require_once 'movies.php';
			break;
		case 'controllers\PostController':
			require_once 'post.php';
			break;
		case 'controllers\ProfileController':
			require_once 'profile.php';
			break;
		case 'controllers\ReportsController':
			require_once 'reports.php';
			break;
		case 'controllers\ServiceLocator':
			require_once 'services.php';
			break;
		case 'controllers\Session':
			require_once 'session.php';
			break;
		case 'controllers\UsersController':
			require_once 'users.php';
			break;
	}
?>
