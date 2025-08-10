# Balinanje - Bocce Ball Tournament Management System

A comprehensive tournament management system for bocce ball competitions, built with WordPress backend and React frontend.

## 🏆 Features

### Tournament Management
- **Swiss System Tournaments**: Automated pairing and round generation
- **Dynamic Round Generation**: Generate rounds one by one based on results
- **Optimal Round Calculation**: Automatic calculation of optimal rounds based on team count
- **Rematch Prevention**: Ensures teams don't play against each other more than once
- **Point Difference Tracking**: Detailed scoring with positive/negative point differences

### Team Management
- **Club/Team Management**: Add, remove, and manage teams
- **Sample Team Generation**: Quick addition of sample teams for testing
- **Individual Team Search**: Search and add specific teams
- **Maximum Team Limits**: Respect tournament capacity limits

### Match Management
- **Real-time Result Entry**: Enter match results with live updates
- **Round Navigation**: View matches by round or all rounds at once
- **Result Validation**: Ensure all results are entered before generating new rounds
- **Tournament Brackets**: Visual bracket view showing tournament progression

### Standings & Rankings
- **Live Standings**: Real-time tournament standings
- **Detailed Statistics**: Points, wins, draws, losses, and point differences
- **Tie-breaking**: Proper Swiss system tie-breaking with point differences
- **Visual Rankings**: Trophy icons and highlighting for top 3 places

## 🛠️ Technology Stack

### Backend
- **WordPress**: Custom plugin with REST API endpoints
- **PHP**: Custom post types and Swiss system algorithms
- **MySQL**: Data storage for tournaments, clubs, players, and matches

### Frontend
- **React.js**: Modern UI components and state management
- **Bootstrap 5**: Responsive design and UI components
- **Axios**: HTTP client for API communication
- **React Router**: Client-side routing

## 📁 Project Structure

```
balinanjemagisterij/
├── app/public/wp-content/plugins/obzg.php          # WordPress plugin
├── obzg-react-frontend/                            # React frontend
│   ├── src/
│   │   ├── components/                             # React components
│   │   ├── services/                               # API services
│   │   └── App.js                                  # Main app component
│   └── package.json
└── README.md
```

## 🚀 Quick Start

### Prerequisites
- WordPress installation (Local by Flywheel recommended)
- Node.js and npm
- Git

### Backend Setup (WordPress)
1. Install the `obzg.php` plugin in your WordPress installation
2. Activate the plugin
3. Access the admin menu to manage tournaments

### Frontend Setup (React)
1. Navigate to the React frontend directory:
   ```bash
   cd obzg-react-frontend
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start the development server:
   ```bash
   npm start
   ```

4. Open [http://localhost:3000](http://localhost:3000) in your browser

## 🔧 Configuration

### API Configuration
Update the API base URL in `obzg-react-frontend/src/services/api.js`:
```javascript
const API_BASE_URL = 'http://your-wordpress-site.local/wp-json/obzg/v1';
```

### CORS Configuration
The WordPress plugin includes CORS headers for cross-origin requests from the React frontend.

## 📊 Swiss System Algorithm

The tournament system implements a proper Swiss system with:
- **Pairing Logic**: Winners play winners, losers play losers
- **Rematch Prevention**: Teams never play the same opponent twice
- **Tie-breaking**: Points → Point Difference → Wins → Draws
- **Optimal Rounds**: Calculated as ⌈log₂(N)⌉ for N teams

## 🎯 Usage

### Creating a Tournament
1. Go to the React frontend
2. Click "Add Tournament"
3. Fill in tournament details (name, dates, location, max teams)
4. Save the tournament

### Managing Teams
1. Navigate to the tournament details
2. Click "Manage Teams"
3. Add teams individually or use "Add Sample Teams"
4. Teams are automatically added to standings

### Running the Tournament
1. Generate the first round
2. Enter match results
3. Generate subsequent rounds based on results
4. View live standings and detailed statistics
5. Switch between Rounds View and Brackets View for different perspectives

## 🌐 Deployment

### Vercel Deployment
1. Push your code to GitHub
2. Connect your repository to Vercel
3. Configure build settings:
   - Build Command: `cd obzg-react-frontend && npm run build`
   - Output Directory: `obzg-react-frontend/build`
4. Deploy!

### Environment Variables
Set the following environment variables in Vercel:
- `REACT_APP_API_BASE_URL`: Your WordPress REST API URL

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📝 License

This project is licensed under the MIT License.

## 🆘 Support

For issues and questions:
1. Check the existing issues
2. Create a new issue with detailed information
3. Include steps to reproduce the problem

---

**Built with ❤️ for the bocce ball community** 