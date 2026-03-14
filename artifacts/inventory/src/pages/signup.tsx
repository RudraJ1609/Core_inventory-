import { useState } from "react";
import { useQueryClient } from "@tanstack/react-query";
import { useSignup } from "@workspace/api-client-react";
import { Package2, Lock, User, Eye, EyeOff, UserPlus } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card } from "@/components/ui/card";

interface SignupPageProps {
  onSuccess: () => void;
  onLoginClick: () => void;
}

export default function SignupPage({ onSuccess, onLoginClick }: SignupPageProps) {
  const queryClient = useQueryClient();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);
  const [error, setError] = useState("");

  const signupMutation = useSignup({
    mutation: {
      onSuccess: () => {
        queryClient.invalidateQueries();
        onSuccess();
      },
      onError: (err: any) => {
        const message = err?.body?.error ?? err?.message ?? "Something went wrong. Please try again.";
        setError(message);
      },
    },
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError("");

    if (!username.trim()) {
      setError("Please enter a username.");
      return;
    }
    if (username.trim().length < 3) {
      setError("Username must be at least 3 characters.");
      return;
    }
    if (password.length < 6) {
      setError("Password must be at least 6 characters.");
      return;
    }
    if (password !== confirmPassword) {
      setError("Passwords do not match.");
      return;
    }

    signupMutation.mutate({ data: { username: username.trim(), password } });
  };

  return (
    <div className="min-h-screen bg-background flex items-center justify-center p-4">
      <div className="w-full max-w-sm space-y-8">
        {/* Logo */}
        <div className="flex flex-col items-center gap-3">
          <div className="bg-primary/10 p-4 rounded-2xl">
            <Package2 className="w-10 h-10 text-primary" />
          </div>
          <div className="text-center">
            <h1 className="text-3xl font-bold text-foreground">Stockify</h1>
            <p className="text-muted-foreground mt-1 text-sm">Inventory Management System</p>
          </div>
        </div>

        {/* Signup Card */}
        <Card className="p-6 shadow-lg border-border/50">
          <div className="mb-6">
            <h2 className="text-xl font-semibold text-foreground">Create account</h2>
            <p className="text-sm text-muted-foreground mt-1">Sign up to get started</p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            {/* Username */}
            <div className="space-y-2">
              <label className="text-sm font-medium text-foreground" htmlFor="signup-username">
                Username
              </label>
              <div className="relative">
                <User className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <Input
                  id="signup-username"
                  type="text"
                  placeholder="Choose a username"
                  className="pl-9 rounded-xl"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  autoComplete="username"
                  autoFocus
                />
              </div>
            </div>

            {/* Password */}
            <div className="space-y-2">
              <label className="text-sm font-medium text-foreground" htmlFor="signup-password">
                Password
              </label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <Input
                  id="signup-password"
                  type={showPassword ? "text" : "password"}
                  placeholder="At least 6 characters"
                  className="pl-9 pr-10 rounded-xl"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  autoComplete="new-password"
                />
                <button
                  type="button"
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                  onClick={() => setShowPassword(!showPassword)}
                  tabIndex={-1}
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            {/* Confirm Password */}
            <div className="space-y-2">
              <label className="text-sm font-medium text-foreground" htmlFor="signup-confirm">
                Confirm password
              </label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <Input
                  id="signup-confirm"
                  type={showConfirm ? "text" : "password"}
                  placeholder="Repeat your password"
                  className="pl-9 pr-10 rounded-xl"
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  autoComplete="new-password"
                />
                <button
                  type="button"
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                  onClick={() => setShowConfirm(!showConfirm)}
                  tabIndex={-1}
                >
                  {showConfirm ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            {error && (
              <div className="text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-xl px-3 py-2">
                {error}
              </div>
            )}

            <Button
              type="submit"
              className="w-full rounded-xl gap-2"
              disabled={signupMutation.isPending}
            >
              <UserPlus className="w-4 h-4" />
              {signupMutation.isPending ? "Creating account..." : "Create account"}
            </Button>
          </form>

          {/* Link to login */}
          <div className="mt-5 pt-5 border-t border-border/50 text-center">
            <p className="text-sm text-muted-foreground">
              Already have an account?{" "}
              <button
                type="button"
                onClick={onLoginClick}
                className="font-medium text-primary hover:underline underline-offset-4 transition-colors"
              >
                Sign in
              </button>
            </p>
          </div>
        </Card>

        {/* Note about role */}
        <p className="text-center text-xs text-muted-foreground">
          New accounts are created with <span className="font-medium">Guest</span> (view-only) access.
          <br />Contact a manager to get edit permissions.
        </p>
      </div>
    </div>
  );
}
